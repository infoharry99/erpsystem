<?php

namespace App\Services\Lead;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiLeadExtractionService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $this->model = config('services.openai.model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Use OpenAI API to classify incoming email and extract structured shipment details.
     */
    public function extract(string $subject, string $bodyText, ?string $fromName = null, ?string $fromEmail = null): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $systemPrompt = <<<PROMPT
You are an expert Freight & Logistics AI Assistant specializing in analyzing incoming logistics emails for a freight forwarding CRM.

YOUR TASK:
1. Determine if the email is a GENUINE FREIGHT / SHIPMENT INQUIRY (RFQ, rate request, ocean/air/road freight quote request, ex-works pickup inquiry, container pricing).
2. If it is NOT a freight inquiry (e.g., general discussion, spam, bounce notification, newsletter, out-of-office, test email, non-shipment message), set "is_shipment_lead": false.
3. Extract all logistics parameters cleanly into the exact JSON format specified.

OUTPUT REQUIREMENTS:
Return ONLY valid JSON matching this schema:
{
  "is_shipment_lead": true or false,
  "confidence_score": float between 0.0 and 1.0,
  "reason": "Short explanation for classification",
  "ai_summary": "Concise 2-3 sentence executive summary of what is being requested in this inquiry",
  "customer_name": "Sender name or null",
  "customer_phone": "Phone number or null",
  "company_name": "Company name or null",
  "shipment_type": "sea_fcl" | "sea_lcl" | "air_freight" | "road_freight" | "reefer" | "express" | "general",
  "incoterms": "EXW" | "FOB" | "CIF" | "DDP" | "DAP" | "CFR" | "FCA" | "DTP" or null,
  "origin": "Origin location/city/country or null",
  "destination": "Destination location/city/country or null",
  "pol": "Port of Loading / AOL or null",
  "pod": "Port of Discharge / AOD or null",
  "pickup_address": "Exact pickup address or null",
  "delivery_address": "Exact delivery address or null",
  "commodity": "Commodity description or null",
  "weight": "Gross weight or null",
  "dimensions": "Dimensions or null",
  "quantity": "Quantity/cases/boxes or null",
  "pallets": "Pallet count/specs or null",
  "container_type": "Equipment required (e.g. 1x20 GP, 1x20 Reefer, 40 HC) or null",
  "shipment_date": "Cargo readiness date or null"
}
PROMPT;

        $userPrompt = "SENDER NAME: {$fromName}\nSENDER EMAIL: {$fromEmail}\nSUBJECT: {$subject}\n\nEMAIL BODY:\n{$bodyText}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $decoded = json_decode($content, true);

                if (is_array($decoded)) {
                    Log::info("OpenAI Lead Extraction Successful. Classification: " . ($decoded['is_shipment_lead'] ? 'GENUINE LEAD' : 'NORMAL EMAIL'));
                    return $decoded;
                }
            } else {
                Log::warning("OpenAI API response error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("OpenAI Lead Extraction Exception: " . $e->getMessage());
        }

        return null;
    }
}
