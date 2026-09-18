@extends('shipment_leads.layouts.app')

@section('title', 'Multiple Email Accounts Management')
@section('page_title', 'Email Accounts')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.95rem;">
            Connected Mailboxes
        </h6>
        <p class="text-muted m-0" style="font-size: 0.78rem;">
            IMAP email accounts configured for automated email sync and reply detection.
        </p>
    </div>
    <a href="{{ route('shipment-leads.accounts.create') }}" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
        <i class="fa-solid fa-plus me-1"></i> Add Email Account
    </a>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
        <span class="fw-bold text-dark" style="font-size: 0.85rem;">
            All Configured Accounts
        </span>
        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; border-radius: 10px;">
            {{ $accounts->count() }} total
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-leads m-0">
                <thead>
                    <tr>
                        <th style="width: 22%;">Account / Email</th>
                        <th style="width: 20%;">IMAP Server</th>
                        <th style="width: 18%;">Folders</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 12%;">Last Synced</th>
                        <th style="width: 8%;">Leads</th>
                        <th style="width: 8%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #e0f2fe; color: #0284c7; font-size: 0.75rem;">
                                        <i class="fa-solid fa-at"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dark" style="font-size: 0.8125rem;">{{ $acc->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $acc->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark" style="font-size: 0.78rem;">{{ $acc->imap_host }}:{{ $acc->imap_port }}</div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem; border-radius: 4px;">{{ strtoupper($acc->imap_encryption ?: 'NONE') }}</span>
                            </td>
                            <td>
                                <div style="font-size: 0.75rem;">
                                    <span class="text-muted">In:</span> <code class="text-dark">{{ $acc->inbox_folder }}</code>
                                    <span class="text-muted ms-1">Sent:</span> <code class="text-dark">{{ $acc->sent_folder }}</code>
                                </div>
                            </td>
                            <td>
                                @if($acc->status === 'active')
                                    <span class="pill-status pill-replied">
                                        <i class="fa-solid fa-check me-1" style="font-size: 0.65rem;"></i> Active
                                    </span>
                                @else
                                    <span class="pill-status pill-read">
                                        Inactive
                                    </span>
                                @endif
                                @if($acc->last_error)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;" title="{{ $acc->last_error }}">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Error
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    {{ $acc->last_sync_at ? $acc->last_sync_at->diffForHumans() : 'Never' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border" style="font-size: 0.72rem; border-radius: 6px;">
                                    {{ $acc->leads_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('shipment-leads.accounts.edit', $acc->id) }}" class="btn btn-outline-primary btn-sm px-2 py-0.5" style="border-radius: 6px; font-size: 0.72rem;" title="Edit Account">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('shipment-leads.accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this email account?');" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-0.5" style="border-radius: 6px; font-size: 0.72rem;" title="Delete Account">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted" style="font-size: 0.8125rem;">
                                <i class="fa-solid fa-at fa-2x mb-2 text-secondary d-block opacity-50"></i>
                                No email accounts added yet. Click <strong>"Add Email Account"</strong> to connect your company mailbox.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
