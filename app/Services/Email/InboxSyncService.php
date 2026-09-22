<?php

namespace App\Services\Email;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\EmailAttachment;
use App\Services\Lead\LeadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InboxSyncService
{
    protected ImapConnectionService $connectionService;
    protected LeadService $leadService;

    public function __construct(ImapConnectionService $connectionService, LeadService $leadService)
    {
        $this->connectionService = $connectionService;
        $this->leadService = $leadService;
    }

    public function syncInbox(EmailAccount $account): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $stats = [
            'checked' => 0,
            'imported' => 0,
            'leads_created' => 0,
            'skipped' => 0,
        ];

        try {
            $client = $this->connectionService->getClient($account);
            $client->connect();

            $inboxName = $account->inbox_folder ?: 'INBOX';
            $folder = $client->getFolder($inboxName);

            if (!$folder) {
                throw new \Exception("Inbox folder '{$inboxName}' not found for account {$account->email}");
            }

            // Query latest 40 emails descending without fetching bodies upfront to maintain Gmail unread flags
            try {
                $messages = $folder->query()
                    ->leaveUnread()
                    ->setFetchBody(false)
                    ->since(now()->subDays(14))
                    ->setFetchOrderDesc()
                    ->limit(40)
                    ->get();

                if ($messages->count() === 0) {
                    $messages = $folder->query()
                        ->leaveUnread()
                        ->setFetchBody(false)
                        ->all()
                        ->setFetchOrderDesc()
                        ->limit(40)
                        ->get();
                }
            } catch (\Throwable $e) {
                Log::warning("Since query fallback for {$account->email}: " . $e->getMessage());
                $messages = $folder->query()
                    ->leaveUnread()
                    ->setFetchBody(false)
                    ->all()
                    ->setFetchOrderDesc()
                    ->limit(40)
                    ->get();
            }

            $stats['checked'] = count($messages);

            foreach ($messages as $msg) {
                try {
                    $messageId = $msg->getMessageId();
                    $uid = $msg->getUid();

                    // Read-only check of Gmail's original read/unread status before touching body
                    $wasUnreadInGmail = false;
                    try {
                        $wasUnreadInGmail = !$msg->hasFlag('seen');
                    } catch (\Throwable $fe) {
                        $wasUnreadInGmail = false;
                    }
                    $isReadInGmail = !$wasUnreadInGmail;

                    $existingEmail = Email::where('email_account_id', $account->id)
                        ->where(function ($query) use ($messageId, $uid) {
                            if ($messageId) {
                                $query->where('message_id', $messageId);
                            }
                            if ($uid) {
                                $query->orWhere('imap_uid', $uid);
                            }
                        })
                        ->first();

                    if ($existingEmail) {
                        // Reflect current Gmail read/unread status in our system
                        if ($existingEmail->is_read !== $isReadInGmail) {
                            $existingEmail->update(['is_read' => $isReadInGmail]);
                            if ($existingEmail->lead && $existingEmail->lead->is_read !== $isReadInGmail) {
                                $existingEmail->lead->update(['is_read' => $isReadInGmail]);
                            }
                        }

                        // Ensure unread status in Gmail remains completely preserved
                        if ($wasUnreadInGmail) {
                            $this->ensureUnreadInGmail($client, $msg, $uid);
                        }

                        $stats['skipped']++;
                        continue;
                    }

                    // For new incoming emails only, fetch message body
                    try {
                        $msg->parseBody();
                    } catch (\Throwable $be) {
                        Log::warning("Could not parse body for UID {$uid}: " . $be->getMessage());
                    }

                    // Immediately restore unread status in Gmail if originally unread
                    if ($wasUnreadInGmail) {
                        $this->ensureUnreadInGmail($client, $msg, $uid);
                    }

                    $from = $msg->getFrom()[0] ?? null;
                    $fromEmail = $from ? $from->mail : 'unknown@domain.com';
                    $fromName = $from ? $this->decodeHeader($from->personal ?: $fromEmail) : 'Unknown';

                    $to = $msg->getTo()[0] ?? null;
                    $toEmail = $to ? $to->mail : $account->email;

                    $subject = $this->decodeHeader($msg->getSubject() ?: '(No Subject)');
                    $bodyHtml = $msg->getHTMLBody();
                    $bodyText = $msg->getTextBody();
                    $receivedDate = $msg->getDate() ? Carbon::parse($msg->getDate()->toString())->setTimezone(config('app.timezone', 'Europe/London')) : now();

                    $emailRecord = Email::create([
                        'email_account_id' => $account->id,
                        'message_id' => $messageId,
                        'imap_uid' => $uid,
                        'thread_id' => $msg->getThreadId() ?? $messageId,
                        'direction' => 'incoming',
                        'from_name' => $fromName,
                        'from_email' => $fromEmail,
                        'to_email' => $toEmail,
                        'cc' => json_encode($msg->getCc()),
                        'bcc' => json_encode($msg->getBcc()),
                        'subject' => $subject,
                        'body_html' => $bodyHtml,
                        'body_text' => $bodyText,
                        'in_reply_to' => $msg->getInReplyTo(),
                        'references' => is_array($msg->getReferences()) ? implode(' ', $msg->getReferences()) : $msg->getReferences(),
                        'received_at' => $receivedDate,
                        'has_attachments' => $msg->hasAttachments(),
                        'is_read' => $isReadInGmail,
                    ]);

                    $stats['imported']++;

                    if ($msg->hasAttachments()) {
                        foreach ($msg->getAttachments() as $attachment) {
                            EmailAttachment::create([
                                'email_id' => $emailRecord->id,
                                'filename' => $attachment->getName() ?: 'attachment',
                                'mime_type' => $attachment->getMimeType(),
                                'file_size' => $attachment->getSize() ?: 0,
                            ]);
                        }
                    }

                    // Double-verify Gmail unread status is preserved after attachments
                    if ($wasUnreadInGmail) {
                        $this->ensureUnreadInGmail($client, $msg, $uid);
                    }

                    $lead = $this->leadService->createLeadFromEmail($emailRecord);
                    if ($lead && $lead->wasRecentlyCreated) {
                        $stats['leads_created']++;
                    }
                } catch (\Throwable $e) {
                    Log::warning("Skipped message during inbox sync: " . $e->getMessage());
                } finally {
                    unset($msg);
                }
            }

            unset($messages);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            $client->disconnect();
            $account->update(['last_error' => null]);
        } catch (\Throwable $e) {
            Log::error("Inbox sync failed for account {$account->email}: " . $e->getMessage());
            $account->update(['last_error' => $e->getMessage()]);
        }

        return $stats;
    }

    /**
     * Ensure an email message remains UNREAD in Gmail after IMAP inspection/fetching.
     */
    protected function ensureUnreadInGmail($client, $msg, $uid): void
    {
        // 1. Webklex Message unsetFlag
        try {
            if ($msg && method_exists($msg, 'unsetFlag')) {
                $msg->unsetFlag('Seen');
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // 2. Direct IMAP STORE command on connection
        try {
            if ($client && method_exists($client, 'getConnection') && $client->getConnection()) {
                $client->getConnection()->store(['\\Seen'], (int)$uid, (int)$uid, '-', true, \Webklex\PHPIMAP\IMAP::ST_UID);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // 3. Native PHP imap_clearflag_full if legacy stream is active
        try {
            if (function_exists('imap_clearflag_full') && $client && method_exists($client, 'getConnection') && $client->getConnection() && method_exists($client->getConnection(), 'getStream')) {
                $stream = $client->getConnection()->getStream();
                if ($stream) {
                    @imap_clearflag_full($stream, (string)$uid, "\\Seen", defined('ST_UID') ? ST_UID : 1);
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }
    }

    protected function decodeHeader(?string $str): string
    {
        if (empty($str)) {
            return '';
        }
        if (function_exists('mb_decode_mime_header')) {
            $str = mb_decode_mime_header($str);
        } elseif (function_exists('iconv_mime_decode')) {
            $str = iconv_mime_decode($str, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        }
        return trim($str, " \t\n\r\0\x0B\"'");
    }
}
