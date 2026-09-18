<?php

namespace App\Console\Commands;

use App\Services\Lead\LeadService;
use Illuminate\Console\Command;

class PruneNonLeadsCommand extends Command
{
    protected $signature = 'leads:prune-non-leads';

    protected $description = 'Scan and remove non-inquiry emails (internal emails, billing, payments, memberships, newsletters) that were mistakenly created as leads.';

    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        parent::__construct();
        $this->leadService = $leadService;
    }

    public function handle(): int
    {
        $this->info('Scanning shipment leads for false-positive non-inquiry emails...');

        $deletedCount = $this->leadService->pruneNonLeads();

        if ($deletedCount > 0) {
            $this->info("Successfully removed {$deletedCount} non-inquiry lead(s).");
        } else {
            $this->info('No non-inquiry leads found. Database is clean!');
        }

        return Command::SUCCESS;
    }
}
