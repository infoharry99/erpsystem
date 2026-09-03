<?php

namespace App\Services\Lead;

use App\Models\ShipmentLead\Email;
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

        $extracted = null;
        $isGenuineLead = false;

        // 1. Try AI-powered OpenAI Extraction if API Key is configured
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

        // 2. Fallback to Rule-based Extraction Service if OpenAI was not available or failed
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
            'lead_status' => 'new',
            'reply_status' => 'not_replied',
        ]);

        Log::info("Created Genuine Shipment Lead ID #{$lead->id} from Email ID #{$email->id}");

        return $lead;
    }

    /**
     * Determine if an incoming email is a genuine shipment inquiry (Rule-based Fallback).
     */
    public function isShipmentInquiry(array $extracted, string $subject, string $bodyText): bool
    {
        $content = strtolower($subject . ' ' . $bodyText);

        // Ignore common non-inquiry emails (System Bounces, Auto-replies, Password Resets, Test Spam)
        if (preg_match('/(undeliverable|delivery status notification|mail delivery system|out of office|auto-reply|autoreply|password reset|verify your email|faltu|test email)/i', $content)) {
            return false;
        }

        // 1. Genuine if key logistics parameters were extracted
        $hasExtractedSpecs = !empty($extracted['incoterms'])
            || !empty($extracted['origin'])
            || !empty($extracted['destination'])
            || !empty($extracted['pol'])
            || !empty($extracted['pod'])
            || !empty($extracted['commodity'])
            || !empty($extracted['weight'])
            || !empty($extracted['container_type'])
            || !empty($extracted['pickup_address'])
            || !empty($extracted['delivery_address']);

        if ($hasExtractedSpecs) {
            return true;
        }

        // 2. Genuine if content contains shipment / logistics / freight keywords
        $keywordsPattern = '/\b(' . implode('|', [
            'rfq', 'quote', 'quotation', 'enquiry', 'inquiry', 'rate', 'rates', 'pricing', 'charges', 'proposal',
            'freight', 'ocean', 'air', 'sea', 'road', 'trucking', 'container', 'shipment', 'cargo',
            'lcl', 'fcl', 'reefer', 'haulier', 'trailer', 'teu', 'cbm', 'pallet', 'pallets',
            'exw', 'fob', 'cif', 'ddp', 'dap', 'cfr', 'fca', 'pickup', 'delivery', 'pol', 'pod', 'aol', 'aod',
            'ex-works', 'ex-w', 'gross weight', 'dimensions', 'port of loading', 'port of discharge'
        ]) . ')\b/i';

        return (bool) preg_match($keywordsPattern, $content);
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
}
