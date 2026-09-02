@extends('shipment_leads.layouts.app')

@section('title', 'Multiple Email Accounts Management')
@section('page_title', 'Email Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted m-0">Manage connected company email accounts for IMAP email synchronization and reply tracking.</p>
    <a href="{{ route('shipment-leads.accounts.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Add Email Account
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Account Name</th>
                        <th>Email Address</th>
                        <th>IMAP Server</th>
                        <th>Inbox / Sent Folders</th>
                        <th>Status</th>
                        <th>Last Synced</th>
                        <th>Leads Imported</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                        <tr>
                            <td><strong>{{ $acc->name }}</strong></td>
                            <td><span class="text-primary">{{ $acc->email }}</span></td>
                            <td><small class="text-muted">{{ $acc->imap_host }}:{{ $acc->imap_port }} ({{ strtoupper($acc->imap_encryption ?: 'NONE') }})</small></td>
                            <td><small><span class="badge bg-light text-dark border">{{ $acc->inbox_folder }}</span> / <span class="badge bg-light text-dark border">{{ $acc->sent_folder }}</span></small></td>
                            <td>
                                @if($acc->status === 'active')
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                                @if($acc->last_error)
                                    <span class="badge bg-danger ms-1" title="{{ $acc->last_error }}"><i class="fa-solid fa-triangle-exclamation"></i> Error</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $acc->last_sync_at ? $acc->last_sync_at->diffForHumans() : 'Never' }}</small></td>
                            <td><span class="badge bg-info text-dark">{{ $acc->leads_count }} leads</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('shipment-leads.accounts.edit', $acc->id) }}" class="btn btn-outline-primary" title="Edit Account">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <form action="{{ route('shipment-leads.accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this email account?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Account">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-at fa-3x mb-3 text-secondary d-block"></i>
                                No email accounts added yet. Click "Add Email Account" above to connect your company mailbox.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
