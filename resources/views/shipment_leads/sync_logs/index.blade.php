@extends('shipment_leads.layouts.app')

@section('title', 'Email Sync History Logs')
@section('page_title', 'Email Sync History')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-dark">
            <i class="fa-solid fa-clock-rotate-left me-2"></i> Synchronization Logs
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Log ID</th>
                        <th>Email Account</th>
                        <th>Started</th>
                        <th>Finished</th>
                        <th>Emails Checked</th>
                        <th>Emails Imported</th>
                        <th>Leads Created</th>
                        <th>Replies Detected</th>
                        <th>Duplicates Skipped</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>#{{ $log->id }}</td>
                            <td><strong>{{ $log->account->email ?? 'Account #' . $log->email_account_id }}</strong></td>
                            <td><small class="text-muted">{{ $log->sync_started_at ? $log->sync_started_at->format('M d, H:i:s') : '-' }}</small></td>
                            <td><small class="text-muted">{{ $log->sync_finished_at ? $log->sync_finished_at->format('M d, H:i:s') : '-' }}</small></td>
                            <td>{{ number_format($log->emails_checked) }}</td>
                            <td><strong class="text-primary">{{ number_format($log->emails_imported) }}</strong></td>
                            <td><strong class="text-success">{{ number_format($log->leads_created) }}</strong></td>
                            <td><strong class="text-info">{{ number_format($log->replies_detected) }}</strong></td>
                            <td><span class="text-muted">{{ number_format($log->skipped_duplicates) }}</span></td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Success</span>
                                @else
                                    <span class="badge bg-danger" title="{{ $log->error_message }}"><i class="fa-solid fa-triangle-exclamation me-1"></i> Failed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">No synchronization logs available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div class="small text-muted mb-2 mb-md-0">
            Showing <strong>{{ $logs->firstItem() ?? 0 }}</strong> to <strong>{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ $logs->total() }}</strong> logs
        </div>
        <div>
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
