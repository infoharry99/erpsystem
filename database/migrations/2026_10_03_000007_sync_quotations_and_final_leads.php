<?php

use App\Services\Lead\LeadService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Backfill all existing leads to ensure any lead whose subject contains QGLT
     * is classified as 'quotation_sent' and QGLT + GLT is 'final_lead'.
     */
    public function up(): void
    {
        try {
            $leadService = app(LeadService::class);
            $updated = $leadService->syncAllLeadStages();
            Log::info("Migration 2026_10_03_000007: Synced {$updated} leads into quotation_sent and final_lead stages.");
        } catch (\Throwable $e) {
            Log::warning('Stage sync migration notice: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
