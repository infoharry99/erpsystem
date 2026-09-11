<?php

namespace App\Console\Commands;

use App\Services\Lead\LeadService;
use Illuminate\Console\Command;

class DeduplicateLeadsCommand extends Command
{
    protected $signature = 'leads:deduplicate';

    protected $description = 'Merge and deduplicate leads with identical email subjects so each subject counts as only one lead.';

    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        parent::__construct();
        $this->leadService = $leadService;
    }

    public function handle(): int
    {
        $this->info('Scanning shipment leads for duplicate email subjects...');

        $deletedCount = $this->leadService->deduplicateExistingLeads();

        if ($deletedCount > 0) {
            $this->info("Successfully merged and removed {$deletedCount} duplicate lead(s).");
        } else {
            $this->info('No duplicate leads found. All lead subjects are unique.');
        }

        return Command::SUCCESS;
    }
}
