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

            $messages = $folder->query()->all()->get();
            $stats['checked'] = count($messages);

            foreach ($messages as $msg) {
                $messageId = $msg->getMessageId();
                $uid = $msg->getUid();

                $exists = Email::where('email_account_id', $account->id)
                    ->where(function ($query) use ($messageId, $uid) {
                        if ($messageId) {
                            $query->where('message_id', $messageId);
                        }
                        if ($uid) {
                            $query->orWhere('imap_uid', $uid);
                        }
                    })
                    ->exists();

                if ($exists) {
                    $stats['skipped']++;
                    continue;
                }

                $from = $msg->getFrom()[0] ?? null;
                $fromEmail = $from ? $from->mail : 'unknown@domain.com';
                $fromName = $from ? $this->decodeHeader($from->personal ?: $fromEmail) : 'Unknown';

                $to = $msg->getTo()[0] ?? null;
                $toEmail = $to ? $to->mail : $account->email;

                $subject = $this->decodeHeader($msg->getSubject() ?: '(No Subject)');
                $bodyHtml = $msg->getHTMLBody();
                $bodyText = $msg->getTextBody();
                $receivedDate = $msg->getDate() ? Carbon::parse($msg->getDate()->toString()) : now();

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

                $lead = $this->leadService->createLeadFromEmail($emailRecord);
                if ($lead) {
                    $stats['leads_created']++;
                }
            }

            $client->disconnect();
            $account->update(['last_error' => null]);
        } catch (\Exception $e) {
            Log::error("Inbox sync failed for account {$account->email}: " . $e->getMessage());
            $account->update(['last_error' => $e->getMessage()]);
        }

        return $stats;
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
