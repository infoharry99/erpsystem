<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\EmailSyncLog;
use App\Services\Email\InboxSyncService;
use App\Services\Email\SentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailSyncController extends Controller
{
    protected InboxSyncService $inboxSyncService;
    protected SentSyncService $sentSyncService;

    public function __construct(InboxSyncService $inboxSyncService, SentSyncService $sentSyncService)
    {
        $this->inboxSyncService = $inboxSyncService;
        $this->sentSyncService = $sentSyncService;
    }

    public function sync(Request $request)
    {
        $lock = Cache::lock('shipment_email_sync_lock', 30);

        if (!$lock->get()) {
            // Force clear lock if request is manual refresh after 30 seconds
            Cache::forget('shipment_email_sync_lock');
            $lock = Cache::lock('shipment_email_sync_lock', 30);
            if (!$lock->get()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Synchronization is already running in another process. Please wait a few seconds.',
                ], 429);
            }
        }

        try {
            $accountId = $request->input('account_id');
            $query = EmailAccount::where('status', 'active');
            if ($accountId) {
                $query->where('id', $accountId);
            }

            $accounts = $query->get();

            if ($accounts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active email accounts found to synchronize.',
                    'stats' => ['checked' => 0, 'imported' => 0, 'leads' => 0, 'replies' => 0, 'skipped' => 0]
                ]);
            }

            $totalChecked = 0;
            $totalImported = 0;
            $totalLeads = 0;
            $totalReplies = 0;
            $totalSkipped = 0;

            foreach ($accounts as $account) {
                $startTime = now();

                try {
                    $inboxStats = $this->inboxSyncService->syncInbox($account);
                    $sentStats = $this->sentSyncService->syncSent($account);

                    $checked = $inboxStats['checked'] + $sentStats['checked'];
                    $imported = $inboxStats['imported'] + $sentStats['imported'];
                    $leads = $inboxStats['leads_created'];
                    $replies = $sentStats['replies_detected'];
                    $skipped = $inboxStats['skipped'];

                    $totalChecked += $checked;
                    $totalImported += $imported;
                    $totalLeads += $leads;
                    $totalReplies += $replies;
                    $totalSkipped += $skipped;

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
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $account->update(['last_error' => $errorMessage]);

                    EmailSyncLog::create([
                        'email_account_id' => $account->id,
                        'sync_started_at' => $startTime,
                        'sync_finished_at' => now(),
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Email synchronization completed!',
                'stats' => [
                    'checked' => $totalChecked,
                    'imported' => $totalImported,
                    'leads' => $totalLeads,
                    'replies' => $totalReplies,
                    'skipped' => $totalSkipped,
                ],
                'last_sync_formatted' => now()->format('Y-m-d H:i:s'),
            ]);
        } finally {
            $lock->release();
        }
    }

    public function history()
    {
        $logs = EmailSyncLog::with('account')->latest()->paginate(20);
        return view('shipment_leads.sync_logs.index', compact('logs'));
    }
}
