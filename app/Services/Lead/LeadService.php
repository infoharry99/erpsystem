<?php

namespace App\Services\Lead;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\ExcludedDomain;
use App\Models\ShipmentLead\ExcludedKeyword;
use App\Models\ShipmentLead\Lead;
use Illuminate\Support\Facades\Log;

class LeadService
{
    protected ShipmentExtractionService $extractionService;
    protected OpenAiLeadExtractionService $openAiService;

    public function __construct(
        ShipmentExtractionService $extractionService,
        OpenAiLeadExtractionService $openAiService
    ) {
        $this->extractionService = $extractionService;
        $this->openAiService = $openAiService;
    }

    public function createLeadFromEmail(Email $email): ?Lead
    {
        $existingLead = Lead::where('email_id', $email->id)->first();
        if ($existingLead) {
            return $existingLead;
        }

        $subject = $email->subject ?? '';
        $bodyText = $email->body_text ?: strip_tags($email->body_html ?? '');

        // 1. HARD EXCLUSION: Reject internal emails, financial/invoices, admin/memberships, newsletters, marketing, operations
        if ($this->isNonLeadEmail($email, $subject, $bodyText)) {
            Log::info("Filter classified email ID #{$email->id} (Subject: '{$subject}', Sender: '{$email->from_email}') as NON-INQUIRY / NON-LEAD. Skipping lead creation.");
            return null;
        }

        // Prevent duplicate leads if a lead with the same email subject already exists
        $existingLeadBySubject = $this->findExistingLeadBySubject($subject, $email);
        if ($existingLeadBySubject) {
            $newStage = Lead::detectSubjectStage($subject);
            $stageUpdates = [];
            if ($newStage === 'final_lead' && $existingLeadBySubject->lead_status !== 'final_lead') {
                $stageUpdates['lead_status'] = 'final_lead';
                $stageUpdates['email_subject'] = $subject;
            } elseif ($newStage === 'quotation_sent' && in_array($existingLeadBySubject->lead_status, ['new', 'not_replied', 'replied'])) {
                $stageUpdates['lead_status'] = 'quotation_sent';
                $stageUpdates['email_subject'] = $subject;
            }
            if (!empty($stageUpdates)) {
                $existingLeadBySubject->update($stageUpdates);
            }

            Log::info("Lead with same email subject already exists (Lead ID #{$existingLeadBySubject->id}, Subject: '{$existingLeadBySubject->email_subject}'). Skipping duplicate lead creation for Email ID #{$email->id}.");
            return $existingLeadBySubject;
        }

        $extracted = null;
        $isGenuineLead = false;

        // 2. Try AI-powered OpenAI Extraction if API Key is configured
        if ($this->openAiService->isConfigured()) {
            $aiResult = $this->openAiService->extract(
                $subject,
                $bodyText,
                $email->from_name,
                $email->from_email
            );

            if ($aiResult !== null) {
                if (empty($aiResult['is_shipment_lead'])) {
                    Log::info("OpenAI classified email ID #{$email->id} (Subject: '{$subject}') as NORMAL/NON-INQUIRY EMAIL. Skipping lead creation.");
                    return null;
                }

                $isGenuineLead = true;
                $extracted = $aiResult;
            }
        }

        // 3. Fallback to Rule-based Extraction Service if OpenAI was not available or failed
        if ($extracted === null) {
            $extracted = $this->extractionService->extract(
                $subject,
                $bodyText,
                $email->from_name,
                $email->from_email
            );

            $isGenuineLead = $this->isShipmentInquiry($extracted, $subject, $bodyText);
        }

        // Skip non-shipment / normal emails
        if (!$isGenuineLead) {
            Log::info("Rule filter classified email ID #{$email->id} (Subject: '{$subject}') as NON-INQUIRY. Skipping lead creation.");
            return null;
        }

        $sanitizedContent = $this->sanitizeHtml($email->body_html ?: nl2br(e($bodyText)));

        $lead = Lead::create([
            'email_id' => $email->id,
            'email_account_id' => $email->email_account_id,
            'customer_name' => $extracted['customer_name'] ?? $email->from_name ?? 'Unknown',
            'customer_email' => $email->from_email,
            'customer_phone' => $extracted['customer_phone'] ?? null,
            'company_name' => $extracted['company_name'] ?? null,
            'email_subject' => $email->subject ?? 'No Subject',
            'ai_summary' => $extracted['ai_summary'] ?? null,
            'original_content' => $sanitizedContent,
            'received_date' => $email->received_at ?? $email->created_at,
            'shipment_type' => $extracted['shipment_type'] ?? 'unknown',
            'origin' => $extracted['origin'] ?? null,
            'destination' => $extracted['destination'] ?? null,
            'pol' => $extracted['pol'] ?? null,
            'pod' => $extracted['pod'] ?? null,
            'pickup_address' => $extracted['pickup_address'] ?? null,
            'delivery_address' => $extracted['delivery_address'] ?? null,
            'commodity' => $extracted['commodity'] ?? null,
            'weight' => $extracted['weight'] ?? null,
            'dimensions' => $extracted['dimensions'] ?? null,
            'quantity' => $extracted['quantity'] ?? null,
            'pallets' => $extracted['pallets'] ?? null,
            'container_type' => $extracted['container_type'] ?? null,
            'shipment_date' => $extracted['shipment_date'] ?? null,
            'incoterms' => $extracted['incoterms'] ?? null,
            'lead_status' => Lead::detectSubjectStage($email->subject ?? ''),
            'reply_status' => 'not_replied',
            'is_read' => (bool) ($email->is_read ?? false),
        ]);

        Log::info("Created Genuine Shipment Lead ID #{$lead->id} from Email ID #{$email->id}");

        return $lead;
    }

    /**
     * Determine if an incoming email is definitely NOT a shipment lead.
     */
    public function isNonLeadEmail(?Email $email, string $subject, string $bodyText, ?string $senderEmail = null): bool
    {
        $fromEmail = strtolower(trim($senderEmail ?? $email->from_email ?? ''));
        $subjectLower = strtolower(trim($subject));
        $fullContent = $subjectLower . ' ' . strtolower(trim($bodyText));

        // 0. Excluded Domains table check (e.g. mesk.com, maersk.com)
        if (!empty($fromEmail) && ExcludedDomain::isDomainExcluded($fromEmail)) {
            Log::info("Filter classified email from '{$fromEmail}' as EXCLUDED DOMAIN. Skipping lead creation.");
            return true;
        }

        // 0.1 Excluded Subject Keywords / Phrases check
        if (!empty($subject) && ExcludedKeyword::isSubjectExcluded($subject)) {
            Log::info("Filter classified email (Subject: '{$subject}') as EXCLUDED KEYWORD/PHRASE. Skipping lead creation.");
            return true;
        }

        // 1. Internal colleague emails (sender domain matches mailbox account domain)
        $accountEmail = strtolower(trim($email->account->email ?? ''));
        if (!empty($accountEmail) && str_contains($accountEmail, '@') && str_contains($fromEmail, '@')) {
            $accDomain = substr(strrchr($accountEmail, "@"), 1);
            $fromDomain = substr(strrchr($fromEmail, "@"), 1);
            if (!empty($accDomain) && $accDomain === $fromDomain) {
                return true;
            }
        }
        if (str_ends_with($fromEmail, '@globetrottersltd.com') || str_ends_with($fromEmail, '@globetrotters.co.uk')) {
            return true;
        }

        // 2. Automated system, newsletter, no-reply, report senders
        if (preg_match('/^(noreply|no-reply|donotreply|notification|notifications|mailer-daemon|postmaster|bounce|news|newsletter|marketing|agencymanagement|billing|accounts|invoice|invoices|support)@/i', $fromEmail)) {
            return true;
        }
        if (str_contains($fromEmail, 'report.') || str_contains($fromEmail, 'e.maersk.com') || str_contains($fromEmail, 'wcabroadcast.com') || str_contains($fromEmail, 'iata.org') || str_contains($fromEmail, 'hmrc.gov.uk') || str_contains($fromEmail, 'messages.ee.co.uk')) {
            return true;
        }

        // 3. Financial, Payments, Invoices, Statements, Reminders, Banking
        $financePattern = '/\b(payment|statement|soa\b|bank\s+confirmation|outstanding|invoice|receipt|overdue|remittance|payroll|american\s+express|credit\s+note|unpaid|tax\s+invoice|due\s+reminder|wire\s+transfer|prepayment\s+advice|balance\s+payable)\b/i';
        if (preg_match($financePattern, $subjectLower)) {
            return true;
        }

        // 4. Admin, Insurance, Legal, Membership, Document Signing, KYC
        $adminPattern = '/\b(membership|insurance|trade\s+credit|agreement|contract|e-stamp|sign\s+plus|due\s+diligence|kyc|certificate|subscription|password\s+reset|verification|wolverine|marvel)\b/i';
        if (preg_match($adminPattern, $subjectLower)) {
            return true;
        }

        // 5. Operations updates (Already booked, Pre-alerts, Arrival notices, Delivery orders)
        $opsPattern = '/\b(pre\s*alert|pre-alert|booking\s+confirmation|arrival\s+notice|delivery\s+order|shipping\s+instruction|bl\s+draft|draft\s+bl|hbl\s+draft|shipment\s+clearance)\b/i';
        if (preg_match($opsPattern, $subjectLower)) {
            return true;
        }

        // 6. Newsletters & Market Updates
        $marketingPattern = '/\b(monthly\s+report|weekly\s+report|market\s+report|market\s+update|advisory|insights|are\s+you\s+prepared|holiday\s+notice|customer\s+advisory|webinar|partner\s+in)\b/i';
        if (preg_match($marketingPattern, $subjectLower)) {
            return true;
        }

        // 7. General system / test spam
        if (preg_match('/(undeliverable|delivery status notification|mail delivery system|out of office|auto-reply|autoreply|password reset|verify your email|faltu|test email)/i', $fullContent)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if an incoming email is a genuine shipment inquiry (Rule-based Fallback).
     */
    public function isShipmentInquiry(array $extracted, string $subject, string $bodyText): bool
    {
        $subjectLower = strtolower($subject);
        $content = strtolower($subject . ' ' . $bodyText);

        // 1. MUST HAVE: Inquiry Intent (RFQ, Quote, Rate, Pricing, Enquiry, QGLT)
        $hasInquiryIntent = (bool) preg_match('/\b(rfq|quote|quotation|rate|rates|pricing|cost|charges|inquiry|enquiry|enq\b|request|tariff)\b/i', $subjectLower)
            || (bool) preg_match('/(?<![A-Za-z0-9])(rfq|qglt)/i', $subjectLower)
            || (bool) preg_match('/\b(please quote|quote request|rate request|rfq|quotation|pricing for|freight cost|best quote|air freight rate|ocean freight rate|fcl rate|lcl rate|enquiry below|inquiry below|charges for the enquiry|freight charges)\b/i', $content);

        if (!$hasInquiryIntent) {
            return false;
        }

        // 2. MUST HAVE: At least one concrete logistics specification
        // Container equipment, weight/volume, incoterm, or valid ports
        $hasEquipment = !empty($extracted['container_type']) || (bool) preg_match('/\b(20\'|40\'|20ft|40ft|gp|hc|hq|fcl|lcl|reefer|teu|cbm)\b/i', $content);
        $hasWeight = !empty($extracted['weight']) || !empty($extracted['pallets']) || (bool) preg_match('/\b(kg|kgs|ton|tons|pallets|gross weight|dimensions)\b/i', $content);
        $hasIncoterms = !empty($extracted['incoterms']);
        $hasRoute = (!empty($extracted['pol']) && !empty($extracted['pod']))
            || (!empty($extracted['origin']) && !empty($extracted['destination']))
            || (!empty($extracted['pickup_address']) || !empty($extracted['delivery_address']));

        return $hasEquipment || $hasWeight || $hasIncoterms || $hasRoute;
    }

    public function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $clean = preg_replace('/on[a-z]+\s*=\s*(["\']).*?\1/i', '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);

        return $clean;
    }

    /**
     * Normalize email subjects by stripping Re:, Fwd:, FW: prefixes and tags.
     */
    public static function normalizeSubject(?string $subject): string
    {
        if (empty($subject)) {
            return '';
        }

        $cleaned = trim($subject);

        // Repeatedly remove Re:, Fwd:, FW:, Aw:, Sv:, Vs: and tags like [External], [EXT], [Spam]
        $pattern = '/^(\s*(\[(external|ext|spam)\]|(re|fwd|fw|sv|vs|aw)\s*(\[\d+\])?\s*:)\s*)+/i';
        while (preg_match($pattern, $cleaned)) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }

        // Collapse multiple whitespace characters into a single space
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return strtolower(trim($cleaned));
    }

    /**
     * Find an existing lead with the same email subject.
     */
    public function findExistingLeadBySubject(?string $subject, ?Email $email = null): ?Lead
    {
        if (empty($subject)) {
            return null;
        }

        $rawTrimmed = trim($subject);
        $normalized = self::normalizeSubject($rawTrimmed);

        if (empty($normalized)) {
            return null;
        }

        $genericSubjects = ['no subject', '(no subject)', 'none'];
        if (in_array($normalized, $genericSubjects, true)) {
            if ($email) {
                // If generic subject, only match if same sender
                return Lead::whereRaw('LOWER(customer_email) = ?', [strtolower(trim($email->from_email))])
                    ->where(function ($q) use ($rawTrimmed) {
                        $q->whereRaw('LOWER(TRIM(email_subject)) = ?', [strtolower($rawTrimmed)]);
                    })->latest('received_date')->first();
            }
            return null;
        }

        // 1. Direct match on raw email_subject (case-insensitive)
        $directMatch = Lead::whereRaw('LOWER(TRIM(email_subject)) = ?', [strtolower($rawTrimmed)])->first();
        if ($directMatch) {
            return $directMatch;
        }

        // 2. Scan leads by normalized subject
        $leads = Lead::whereNotNull('email_subject')->get();
        foreach ($leads as $lead) {
            $leadNorm = self::normalizeSubject($lead->email_subject);
            if ($leadNorm === $normalized) {
                return $lead;
            }

            // If the normalized subjects are related on the same thread (e.g. QGLT or GLT code was appended)
            if (strlen($leadNorm) >= 15 && strlen($normalized) >= 15) {
                if ($email && !empty($email->from_email) && !empty($lead->customer_email)
                    && strtolower(trim($email->from_email)) === strtolower(trim($lead->customer_email))) {
                    if (str_starts_with($normalized, $leadNorm) || str_starts_with($leadNorm, $normalized)
                        || str_contains($normalized, $leadNorm) || str_contains($leadNorm, $normalized)) {
                        return $lead;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Deduplicate existing leads by email subject (merges notes/data and removes duplicates).
     *
     * @return int Number of duplicate leads removed
     */
    public function deduplicateExistingLeads(): int
    {
        $leads = Lead::with('leadNotes')->orderBy('id', 'asc')->get();
        $seen = [];
        $deletedCount = 0;

        foreach ($leads as $lead) {
            $normalized = self::normalizeSubject($lead->email_subject);
            if (empty($normalized) || in_array($normalized, ['no subject', '(no subject)', 'none'], true)) {
                continue;
            }

            if (isset($seen[$normalized])) {
                $primaryLead = $seen[$normalized];

                // Reassign any notes from duplicate lead to primary lead
                foreach ($lead->leadNotes as $note) {
                    $note->update(['lead_id' => $primaryLead->id]);
                }

                // Copy over any missing extracted fields from duplicate to primary
                $fieldsToBackfill = [
                    'origin', 'destination', 'shipment_type', 'pol', 'pod',
                    'pickup_address', 'delivery_address', 'commodity', 'weight',
                    'dimensions', 'quantity', 'pallets', 'container_type',
                    'shipment_date', 'incoterms', 'ai_summary', 'customer_phone',
                    'company_name'
                ];
                $updates = [];
                foreach ($fieldsToBackfill as $field) {
                    if (empty($primaryLead->$field) && !empty($lead->$field)) {
                        $updates[$field] = $lead->$field;
                        $primaryLead->$field = $lead->$field;
                    }
                }

                // If duplicate was marked replied, reflect on primary lead
                if ($lead->reply_status === 'replied' && $primaryLead->reply_status !== 'replied') {
                    $updates['reply_status'] = 'replied';
                    $updates['replied_at'] = $lead->replied_at;
                    $updates['replied_by_email_account_id'] = $lead->replied_by_email_account_id;
                    $updates['reply_message_id'] = $lead->reply_message_id;
                }

                // Check if duplicate lead has a more advanced subject stage (final_lead or quotation_sent)
                $primaryStage = Lead::detectSubjectStage($primaryLead->email_subject);
                $dupStage = Lead::detectSubjectStage($lead->email_subject);
                if ($dupStage === 'final_lead' && $primaryStage !== 'final_lead') {
                    $updates['lead_status'] = 'final_lead';
                    $updates['email_subject'] = $lead->email_subject;
                } elseif ($dupStage === 'quotation_sent' && in_array($primaryLead->lead_status, ['new', 'not_replied', 'replied'])) {
                    $updates['lead_status'] = 'quotation_sent';
                    $updates['email_subject'] = $lead->email_subject;
                }

                if (!empty($updates)) {
                    $primaryLead->update($updates);
                }

                // Delete the duplicate lead
                $lead->delete();
                $deletedCount++;
                Log::info("Deduplicated Lead ID #{$lead->id} (Subject: '{$lead->email_subject}') into Primary Lead ID #{$primaryLead->id}");
            } else {
                $seen[$normalized] = $lead;
            }
        }

        return $deletedCount;
    }

    /**
     * Scan and remove non-inquiry leads (internal emails, billing, payments, memberships, newsletters)
     * that were mistakenly created as leads.
     *
     * @return int Number of non-inquiry leads removed
     */
    public function pruneNonLeads(): int
    {
        $leads = Lead::with(['email.account', 'leadNotes'])->get();
        $deletedCount = 0;

        foreach ($leads as $lead) {
            $isNonLead = false;

            // 1. Check against negative non-lead filters
            if ($this->isNonLeadEmail($lead->email, $lead->email_subject ?? '', $lead->original_content ?? '', $lead->customer_email)) {
                $isNonLead = true;
            }

            // 2. Also check if the lead subject / summary clearly reveals it is not an inquiry
            if (!$isNonLead) {
                $subject = strtolower(trim($lead->email_subject ?? ''));
                if (preg_match('/\b(payment|statement|invoice|receipt|membership|e-stamp|agreement|due\s+reminder|monthly\s+report|weekly\s+report|market\s+report|market\s+update)\b/i', $subject)) {
                    $isNonLead = true;
                }
            }

            if ($isNonLead) {
                // Delete associated lead notes
                $lead->leadNotes()->delete();
                $lead->delete();
                $deletedCount++;
                Log::info("Pruned non-inquiry lead ID #{$lead->id} (Subject: '{$lead->email_subject}', Sender: '{$lead->customer_email}')");
            }
        }

        return $deletedCount;
    }

    /**
     * Prune all existing leads created from an excluded domain.
     */
    public function pruneLeadsForDomain(string $domain): int
    {
        $domain = strtolower(trim($domain));
        $domain = ltrim($domain, '@');
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        $leads = Lead::where(function ($query) use ($domain) {
            $query->where('customer_email', 'like', '%@' . $domain)
                  ->orWhere('customer_email', 'like', '%@%.' . $domain);
        })->get();

        $count = 0;
        foreach ($leads as $lead) {
            $lead->leadNotes()->delete();
            $lead->delete();
            $count++;
            Log::info("Pruned lead ID #{$lead->id} because domain '{$domain}' is in Excluded Domains list.");
        }

        return $count;
    }

    /**
     * Prune all existing leads whose email subjects match an excluded keyword or phrase.
     */
    public function pruneLeadsForKeyword(string $keyword): int
    {
        $keyword = strtolower(trim($keyword));
        if (empty($keyword)) {
            return 0;
        }

        $leads = Lead::where('email_subject', 'like', "%{$keyword}%")->get();

        $count = 0;
        foreach ($leads as $lead) {
            $lead->leadNotes()->delete();
            $lead->delete();
            $count++;
            Log::info("Pruned lead ID #{$lead->id} because subject matched excluded keyword '{$keyword}'.");
        }

        return $count;
    }

    /**
     * Synchronize and backfill lead stages across all leads in the database.
     * Moves any lead with QGLT into 'quotation_sent' and QGLT + GLT into 'final_lead'.
     *
     * @return int Number of leads whose status was updated
     */
    public function syncAllLeadStages(): int
    {
        $leads = Lead::all();
        $updatedCount = 0;

        foreach ($leads as $lead) {
            $stage = Lead::detectSubjectStage($lead->email_subject);

            // Also check associated thread emails if subject on lead didn't have the code directly
            if ($stage === 'new' && $lead->email && $lead->email->thread_id) {
                $threadEmails = Email::where('email_account_id', $lead->email_account_id)
                    ->where('thread_id', $lead->email->thread_id)
                    ->get();
                foreach ($threadEmails as $te) {
                    $teStage = Lead::detectSubjectStage($te->subject);
                    if ($teStage === 'final_lead') {
                        $stage = 'final_lead';
                        break;
                    } elseif ($teStage === 'quotation_sent' && $stage !== 'final_lead') {
                        $stage = 'quotation_sent';
                    }
                }
            }

            if ($stage === 'final_lead') {
                if ($lead->lead_status !== 'final_lead') {
                    $lead->update(['lead_status' => 'final_lead', 'reply_status' => 'replied']);
                    $updatedCount++;
                    Log::info("Synced Lead ID #{$lead->id} stage to 'final_lead' (Subject: '{$lead->email_subject}')");
                }
            } elseif ($stage === 'quotation_sent') {
                if ($lead->lead_status !== 'quotation_sent') {
                    $lead->update(['lead_status' => 'quotation_sent', 'reply_status' => 'replied']);
                    $updatedCount++;
                    Log::info("Synced Lead ID #{$lead->id} stage to 'quotation_sent' (Subject: '{$lead->email_subject}')");
                }
            } elseif (in_array($lead->lead_status, ['quotation_sent', 'final_lead'])) {
                // If subject no longer matches QGLT / GLT, revert to replied or new
                $target = ($lead->reply_status === 'replied') ? 'replied' : 'new';
                $lead->update(['lead_status' => $target]);
                $updatedCount++;
                Log::info("Reverted Lead ID #{$lead->id} stage to '{$target}'");
            }
        }

        return $updatedCount;
    }
}

