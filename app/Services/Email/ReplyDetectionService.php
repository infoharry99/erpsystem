<?php

namespace App\Services\Email;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\Lead;
use Illuminate\Support\Facades\Log;

class ReplyDetectionService
{
    public function processOutgoingReply(Email $outgoingEmail): bool
    {
        if ($outgoingEmail->direction !== 'outgoing') {
            return false;
        }

        $matchedLead = null;

        // 1. Primary Match: In-Reply-To header
        if (!empty($outgoingEmail->in_reply_to)) {
            $inReplyTo = trim($outgoingEmail->in_reply_to, '<> ');
            $incomingEmail = Email::where('direction', 'incoming')
                ->where(function ($q) use ($inReplyTo) {
                    $q->where('message_id', $inReplyTo)
                      ->orWhere('message_id', '<' . $inReplyTo . '>');
                })->first();

            if ($incomingEmail && $incomingEmail->lead) {
                $matchedLead = $incomingEmail->lead;
            }
        }

        // 2. Secondary Match: References header
        if (!$matchedLead && !empty($outgoingEmail->references)) {
            $refTokens = preg_split('/\s+/', $outgoingEmail->references);
            foreach ($refTokens as $token) {
                $cleanToken = trim($token, '<> ');
                if (empty($cleanToken)) continue;

                $incomingEmail = Email::where('direction', 'incoming')
                    ->where(function ($q) use ($cleanToken) {
                        $q->where('message_id', $cleanToken)
                          ->orWhere('message_id', '<' . $cleanToken . '>');
                    })->first();

                if ($incomingEmail && $incomingEmail->lead) {
                    $matchedLead = $incomingEmail->lead;
                    break;
                }
            }
        }

        // 3. Fallback Match: Recipient Email + Normalized Subject
        if (!$matchedLead && !empty($outgoingEmail->to_email)) {
            $targetEmail = strtolower(trim($outgoingEmail->to_email));
            $normSubject = $this->normalizeSubject($outgoingEmail->subject);

            $matchedLead = Lead::whereRaw('LOWER(customer_email) = ?', [$targetEmail])
                ->get()
                ->first(function ($lead) use ($normSubject) {
                    return $this->normalizeSubject($lead->email_subject) === $normSubject;
                });
        }

        if ($matchedLead) {
            $matchedLead->update([
                'reply_status' => 'replied',
                'replied_at' => $outgoingEmail->sent_at ?: $outgoingEmail->created_at,
                'replied_by_email_account_id' => $outgoingEmail->email_account_id,
                'reply_message_id' => $outgoingEmail->message_id,
                'lead_status' => in_array($matchedLead->lead_status, ['new', 'not_replied']) ? 'replied' : $matchedLead->lead_status,
            ]);

            Log::info("Detected Reply for Lead ID #{$matchedLead->id} from Outgoing Email ID #{$outgoingEmail->id}");
            return true;
        }

        return false;
    }

    public function normalizeSubject(?string $subject): string
    {
        if (empty($subject)) {
            return '';
        }
        $cleaned = preg_replace('/^(re|fwd|fw|sv|vs|aw)\s*:\s*/i', '', trim($subject));
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        return strtolower($cleaned);
    }
}
