@extends('shipment_leads.layouts.app')

@section('title', 'Email Sync History Logs')
@section('page_title', 'Email Sync History')

@section('content')
<div class="card card-modern shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                <i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> Synchronization Logs
            </span>
            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; border-radius: 10px;">
                {{ $logs->total() }} total runs
            </span>
        </div>
        <button class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;" onclick="triggerEmailSync()">
            <i class="fa-solid fa-rotate me-1"></i> Sync Now
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-leads m-0">
                <thead>
                    <tr>
                        <th style="width: 7%;">Log ID</th>
                        <th style="width: 23%;">Email Account</th>
                        <th style="width: 13%;">Started</th>
                        <th style="width: 13%;">Finished</th>
                        <th style="width: 8%;">Checked</th>
                        <th style="width: 8%;">Imported</th>
                        <th style="width: 8%;">Leads</th>
                        <th style="width: 7%;">Replies</th>
                        <th style="width: 7%;">Duplicates</th>
                        <th style="width: 6%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <span class="fw-bold text-muted" style="font-size: 0.75rem;">#{{ $log->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5 text-truncate">
                                    <i class="fa-regular fa-envelope text-muted" style="font-size: 0.75rem;"></i>
                                    <span class="fw-bold text-dark text-truncate" style="font-size: 0.8125rem;">{{ $log->account->email ?? 'Account #' . $log->email_account_id }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $log->sync_started_at ? $log->sync_started_at->format('M d, H:i:s') : '-' }}</span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $log->sync_finished_at ? $log->sync_finished_at->format('M d, H:i:s') : '-' }}</span>
                            </td>
                            <td>
                                <span class="text-dark" style="font-size: 0.78rem;">{{ number_format($log->emails_checked) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary" style="font-size: 0.78rem;">{{ number_format($log->emails_imported) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success" style="font-size: 0.78rem;">{{ number_format($log->leads_created) }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info" style="font-size: 0.78rem;">{{ number_format($log->replies_detected) }}</span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ number_format($log->skipped_duplicates) }}</span>
                            </td>
                            <td class="text-center">
                                @if($log->status === 'success')
                                    <span class="pill-status pill-replied">
                                        <i class="fa-solid fa-check me-1" style="font-size: 0.65rem;"></i> Success
                                    </span>
                                @else
                                    <span class="pill-status pill-not-replied" title="{{ $log->error_message }}">
                                        <i class="fa-solid fa-triangle-exclamation me-1" style="font-size: 0.65rem;"></i> Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted" style="font-size: 0.8125rem;">
                                No synchronization logs available yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center border-top">
        <div class="text-muted" style="font-size: 0.75rem;">
            Showing <strong>{{ $logs->firstItem() ?? 0 }}</strong> to <strong>{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ $logs->total() }}</strong> logs
        </div>
        <div>
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
