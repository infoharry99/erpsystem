<?php

use App\Services\Lead\LeadService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $leadService = app(LeadService::class);
            $leadService->deduplicateExistingLeads();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Deduplication migration notice: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data merge
    }
};
