<?php

namespace App\Services\Email;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\EmailAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SentSyncService
{
    protected ImapConnectionService $connectionService;
    protected ReplyDetectionService $replyDetectionService;

    public function __construct(ImapConnectionService $connectionService, ReplyDetectionService $replyDetectionService)
    {
        $this->connectionService = $connectionService;
        $this->replyDetectionService = $replyDetectionService;
    }

    public function syncSent(EmailAccount $account): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $stats = [
            'checked' => 0,
            'imported' => 0,
            'replies_detected' => 0,
        ];

        try {
            $client = $this->connectionService->getClient($account);
            $client->connect();

            $sentFolderName = $account->sent_folder ?: 'Sent';
            $folder = $client->getFolder($sentFolderName);

            if (!$folder) {
                $alternatives = ['Sent Items', 'Sent Messages', '[Gmail]/Sent Mail'];
                foreach ($alternatives as $alt) {
                    $folder = $client->getFolder($alt);
                    if ($folder) break;
                }
            }

            if (!$folder) {
                Log::warning("Sent folder '{$sentFolderName}' not found for account {$account->email}");
                return $stats;
            }

            // Query latest 40 sent emails descending using setFetchBody(false) to maintain read-only IMAP state
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
                Log::warning("Sent query fallback for {$account->email}: " . $e->getMessage());
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

                    $exists = Email::where('email_account_id', $account->id)
                        ->where('direction', 'outgoing')
                        ->where(function ($query) use ($messageId, $uid) {
                            if ($messageId) {
                                $query->where('message_id', $messageId);
                            }
                            if ($uid) {
                                $query->orWhere('imap_uid', $uid);
                            }
                        })
                        ->first();

                    if ($exists) {
                        if ($this->replyDetectionService->processOutgoingReply($exists)) {
                            $stats['replies_detected']++;
                        }
                        continue;
                    }

                    // For new outgoing email only, fetch body
                    try {
                        $msg->parseBody();
                    } catch (\Throwable $be) {
                        Log::warning("Could not parse sent body for UID {$uid}: " . $be->getMessage());
                    }

                    $to = $msg->getTo()[0] ?? null;
                    $toEmail = $to ? $to->mail : null;

                    $from = $msg->getFrom()[0] ?? null;
                    $fromEmail = $from ? $from->mail : $account->email;

                    $sentDate = $msg->getDate() ? Carbon::parse($msg->getDate()->toString())->setTimezone(config('app.timezone', 'Europe/London')) : now();

                    $outgoingRecord = Email::create([
                        'email_account_id' => $account->id,
                        'message_id' => $messageId,
                        'imap_uid' => $uid,
                        'thread_id' => $msg->getThreadId() ?? $messageId,
                        'direction' => 'outgoing',
                        'from_name' => $account->name,
                        'from_email' => $fromEmail,
                        'to_email' => $toEmail,
                        'cc' => json_encode($msg->getCc()),
                        'bcc' => json_encode($msg->getBcc()),
                        'subject' => $msg->getSubject() ?: '(No Subject)',
                        'body_html' => $msg->getHTMLBody(),
                        'body_text' => $msg->getTextBody(),
                        'in_reply_to' => $msg->getInReplyTo(),
                        'references' => is_array($msg->getReferences()) ? implode(' ', $msg->getReferences()) : $msg->getReferences(),
                        'sent_at' => $sentDate,
                        'has_attachments' => $msg->hasAttachments(),
                        'is_read' => true,
                    ]);

                    $stats['imported']++;

                    if ($this->replyDetectionService->processOutgoingReply($outgoingRecord)) {
                        $stats['replies_detected']++;
                    }
                } catch (\Throwable $e) {
                    Log::warning("Skipped message during sent folder sync: " . $e->getMessage());
                } finally {
                    unset($msg);
                }
            }

            unset($messages);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            $client->disconnect();
        } catch (\Throwable $e) {
            Log::error("Sent folder sync failed for account {$account->email}: " . $e->getMessage());
        }

        return $stats;
    }
}
