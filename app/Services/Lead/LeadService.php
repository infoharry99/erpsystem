<?php

namespace App\Services\Lead;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\Lead;
use Illuminate\Support\Facades\Log;

class LeadService
{
    protected ShipmentExtractionService $extractionService;

    public function __construct(ShipmentExtractionService $extractionService)
    {
        $this->extractionService = $extractionService;
    }

    public function createLeadFromEmail(Email $email): Lead
    {
        $existingLead = Lead::where('email_id', $email->id)->first();
        if ($existingLead) {
            return $existingLead;
        }

        $bodyText = $email->body_text ?: strip_tags($email->body_html ?? '');
        $extracted = $this->extractionService->extract(
            $email->subject ?? '',
            $bodyText,
            $email->from_name,
            $email->from_email
        );

        $sanitizedContent = $this->sanitizeHtml($email->body_html ?: nl2br(e($bodyText)));

        $lead = Lead::create([
            'email_id' => $email->id,
            'email_account_id' => $email->email_account_id,
            'customer_name' => $extracted['customer_name'] ?? $email->from_name ?? 'Unknown',
            'customer_email' => $email->from_email,
            'customer_phone' => $extracted['customer_phone'] ?? null,
            'company_name' => $extracted['company_name'] ?? null,
            'email_subject' => $email->subject ?? 'No Subject',
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

        Log::info("Created Shipment Lead ID #{$lead->id} from Email ID #{$email->id}");

        return $lead;
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
