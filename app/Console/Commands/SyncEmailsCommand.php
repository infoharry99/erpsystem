<?php

namespace App\Console\Commands;

use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\EmailSyncLog;
use App\Services\Email\InboxSyncService;
use App\Services\Email\SentSyncService;
use Illuminate\Console\Command;

class SyncEmailsCommand extends Command
{
    protected $signature = 'emails:sync {--account= : Specific Email Account ID to sync}';

    protected $description = 'Synchronize active email accounts via IMAP, convert emails to leads, and detect outgoing replies.';

    protected InboxSyncService $inboxSyncService;
    protected SentSyncService $sentSyncService;

    public function __construct(InboxSyncService $inboxSyncService, SentSyncService $sentSyncService)
    {
        parent::__construct();
        $this->inboxSyncService = $inboxSyncService;
        $this->sentSyncService = $sentSyncService;
    }

    public function handle()
    {
        $this->info('Starting Email Synchronization...');

        $accountOption = $this->option('account');
        $query = EmailAccount::where('status', 'active');
        if ($accountOption) {
            $query->where('id', $accountOption);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No active email accounts found.');
            return Command::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->line("Processing account: {$account->email} ({$account->name})");
            $startTime = now();

            try {
                $inboxStats = $this->inboxSyncService->syncInbox($account);
                $sentStats = $this->sentSyncService->syncSent($account);

                $checked = $inboxStats['checked'] + $sentStats['checked'];
                $imported = $inboxStats['imported'] + $sentStats['imported'];
                $leads = $inboxStats['leads_created'];
                $replies = $sentStats['replies_detected'];
                $skipped = $inboxStats['skipped'];

                $account->update(['last_sync_at' => now()]);

                EmailSyncLog::create([
                    'email_account_id' => $account->id,
                    'sync_started_at' => $startTime,
                    'sync_finished_at' => now(),
                    'emails_checked' => $checked,
                    'emails_imported' => $imported,
                    'leads_created' => $leads,
                    'replies_detected' => $replies,
                    'skipped_duplicates' => $skipped,
                    'status' => 'success',
                ]);

                $this->info(" -> Checked: {$checked} | Imported: {$imported} | Leads: {$leads} | Replies: {$replies} | Skipped: {$skipped}");
            } catch (\Exception $e) {
                $this->error(" -> Failed for {$account->email}: " . $e->getMessage());

                EmailSyncLog::create([
                    'email_account_id' => $account->id,
                    'sync_started_at' => $startTime,
                    'sync_finished_at' => now(),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Email Synchronization Completed Successfully!');
        return Command::SUCCESS;
    }
}
