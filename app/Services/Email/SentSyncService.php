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

            try {
                $messages = $folder->query()->since(now()->subDays(30))->get();
                if ($messages->count() === 0) {
                    $messages = $folder->query()->all()->limit(100)->get();
                }
            } catch (\Exception $e) {
                $messages = $folder->query()->all()->limit(100)->get();
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

                    $to = $msg->getTo()[0] ?? null;
                    $toEmail = $to ? $to->mail : null;

                    $from = $msg->getFrom()[0] ?? null;
                    $fromEmail = $from ? $from->mail : $account->email;

                    $sentDate = $msg->getDate() ? Carbon::parse($msg->getDate()->toString()) : now();

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
                    ]);

                    $stats['imported']++;

                    if ($this->replyDetectionService->processOutgoingReply($outgoingRecord)) {
                        $stats['replies_detected']++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Skipped message during sent folder sync: " . $e->getMessage());
                }
            }

            $client->disconnect();
        } catch (\Exception $e) {
            Log::error("Sent folder sync failed for account {$account->email}: " . $e->getMessage());
        }

        return $stats;
    }
}
