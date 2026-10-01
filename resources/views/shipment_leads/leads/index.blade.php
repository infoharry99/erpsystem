@extends('shipment_leads.layouts.app')

@section('title', 'Shipment Leads')
@section('page_title', 'Shipment Leads')

@section('content')
<!-- Filter Card (Modern Minimalist Design) -->
<div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('shipment-leads.leads.index') }}" class="row g-2 align-items-center">
            <div class="col-lg-3 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size: 0.75rem;"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" style="border-radius: 0 8px 8px 0; font-size: 0.8125rem;" placeholder="Search customer, subject, ID..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <select name="email_account_id" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                    <option value="">All Mailboxes</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('email_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <select name="is_read" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                    <option value="">Gmail Status</option>
                    <option value="unread" {{ request('is_read') === 'unread' ? 'selected' : '' }}>✉️ Unread in Gmail</option>
                    <option value="read" {{ request('is_read') === 'read' ? 'selected' : '' }}>📖 Read in Gmail</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <select name="reply_status" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                    <option value="">Reply Status</option>
                    <option value="not_replied" {{ request('reply_status') === 'not_replied' ? 'selected' : '' }}>Not Replied</option>
                    <option value="replied" {{ request('reply_status') === 'replied' ? 'selected' : '' }}>Replied</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <select name="lead_status" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                    <option value="">Lead Status</option>
                    @foreach(['new', 'not_replied', 'replied', 'follow_up', 'quotation_sent', 'negotiation', 'booked', 'won', 'lost', 'spam', 'closed'] as $st)
                        <option value="{{ $st }}" {{ request('lead_status') === $st ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm px-2 flex-grow-1" style="border-radius: 8px;" title="Filter">
                    <i class="fa-solid fa-filter me-1" style="font-size: 0.75rem;"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'email_account_id', 'is_read', 'reply_status', 'lead_status', 'shipment_type', 'sort']))
                    <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="border-radius: 8px;" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left" style="font-size: 0.75rem;"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-2 px-3 border-bottom" style="border-radius: 12px 12px 0 0;">
        <div class="d-flex align-items-center gap-2">
            <h6 class="m-0 fw-bold text-dark" style="font-size: 0.95rem;">
                All Shipment Leads
            </h6>
            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; border-radius: 12px;">
                {{ $leads->total() }} total
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted" style="font-size: 0.75rem;">Sort:</span>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="btn btn-sm {{ request('sort', 'newest') === 'newest' ? 'btn-primary' : 'btn-outline-secondary' }}" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">Newest</a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'not_replied']) }}" class="btn btn-sm {{ request('sort') === 'not_replied' ? 'btn-danger' : 'btn-outline-secondary' }}" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">Unreplied First</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-leads m-0">
                <thead>
                    <tr>
                        <th style="width: 8%;"># / Date</th>
                        <th style="width: 17%;">Customer</th>
                        <th style="width: 23%;">Subject & Summary</th>
                        <th style="width: 15%;">Route (Origin &rarr; Dest)</th>
                        <th style="width: 8%;">Type</th>
                        <th style="width: 11%;">Mailbox</th>
                        <th style="width: 9%;">Status</th>
                        <th style="width: 9%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        @php
                            $initials = '';
                            $nameClean = trim($lead->customer_name ?? '');
                            $nameParts = preg_split('/\s+/', $nameClean);
                            if (!empty($nameParts[0])) $initials .= strtoupper(substr($nameParts[0], 0, 1));
                            if (isset($nameParts[1]) && !empty($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                            if (empty($initials)) $initials = strtoupper(substr($lead->customer_email ?? 'CL', 0, 2));
                            $avatarColorIndex = abs(crc32($lead->customer_email ?? $lead->customer_name ?? '')) % 6;
                        @endphp
                        <tr class="{{ $lead->reply_status === 'not_replied' ? 'lead-unreplied' : '' }}">
                            <!-- # & Date -->
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @if(!$lead->is_read)
                                        <span class="unread-dot" title="Unread in Gmail"></span>
                                    @endif
                                    <span class="fw-bold {{ !$lead->is_read ? 'text-primary' : 'text-dark' }}">#{{ $lead->id }}</span>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem; white-space: nowrap;">
                                    {{ $lead->received_date ? $lead->received_date->format('M d, H:i') : '-' }}
                                </div>
                            </td>

                            <!-- Customer with Initials Avatar -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-initial avatar-bg-{{ $avatarColorIndex }}" title="{{ $lead->customer_name }}">
                                        {{ $initials }}
                                    </div>
                                    <div class="text-truncate" style="min-width: 0;">
                                        <div class="fw-semibold text-dark text-truncate" title="{{ $lead->customer_name }}">
                                            {{ $lead->customer_name ?: 'Unknown Customer' }}
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 0.7rem;" title="{{ $lead->customer_email }}">
                                            {{ $lead->customer_email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Subject & Summary -->
                            <td>
                                <div class="text-truncate">
                                    <span class="{{ !$lead->is_read ? 'fw-bold text-dark' : 'fw-semibold text-secondary' }} text-truncate d-block" title="{{ $lead->email_subject }}">
                                        {{ $lead->email_subject ?: 'No Subject' }}
                                    </span>
                                    @if($lead->ai_summary)
                                        <span class="text-secondary text-truncate d-block" style="font-size: 0.7rem;" title="{{ $lead->ai_summary }}">
                                            <i class="fa-solid fa-wand-magic-sparkles text-primary me-1" style="font-size: 0.65rem;"></i>{{ $lead->ai_summary }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Route -->
                            <td>
                                <div class="pill-route" title="{{ ($lead->origin ?: 'TBD') . ' → ' . ($lead->destination ?: 'TBD') }}">
                                    <span>{{ $lead->origin ?: 'TBD' }}</span>
                                    <i class="fa-solid fa-arrow-right text-muted mx-1" style="font-size: 0.65rem;"></i>
                                    <span>{{ $lead->destination ?: 'TBD' }}</span>
                                </div>
                            </td>

                            <!-- Shipment Type -->
                            <td>
                                <span class="pill-status pill-type">{{ $lead->shipment_type_label }}</span>
                            </td>

                            <!-- Source Mailbox -->
                            <td>
                                <div class="text-muted text-truncate" style="font-size: 0.72rem;" title="{{ $lead->account->email ?? 'N/A' }}">
                                    <i class="fa-regular fa-envelope me-1 text-secondary" style="font-size: 0.7rem;"></i>{{ $lead->account->email ?? 'N/A' }}
                                </div>
                            </td>

                            <!-- Reply, Read & Lead Status -->
                            <td>
                                <div class="d-flex flex-column gap-1 align-items-start">
                                    @if($lead->is_read)
                                        <span class="pill-status pill-read" title="Gmail: Read">
                                            <i class="fa-regular fa-envelope-open me-1" style="font-size: 0.55rem;"></i>Read
                                        </span>
                                    @else
                                        <span class="pill-status pill-unread" title="Gmail: Unread">
                                            <span class="unread-dot me-1" style="width: 5px; height: 5px; min-width: 5px;"></span>Unread
                                        </span>
                                    @endif

                                    @if($lead->reply_status === 'replied')
                                        <span class="pill-status pill-replied">
                                            <i class="fa-solid fa-circle-check me-1" style="font-size: 0.55rem;"></i>Replied
                                        </span>
                                    @else
                                        <span class="pill-status pill-not-replied">
                                            <i class="fa-solid fa-circle-exclamation me-1" style="font-size: 0.55rem;"></i>Not Replied
                                        </span>
                                    @endif

                                    <span class="pill-status pill-lead-status">
                                        {{ str_replace('_', ' ', $lead->lead_status) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="btn-action-open" title="Open Lead Details">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.65rem;"></i> Open
                                    </a>
                                    <button type="button" class="btn-action-not-lead" onclick="openNotLeadModal({{ $lead->id }}, '{{ addslashes($lead->customer_name ?? 'Unknown') }}', '{{ addslashes($lead->customer_email) }}')" title="Mark as Not a Lead">
                                        <i class="fa-solid fa-ban" style="font-size: 0.65rem;"></i> Not Lead
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-2 text-secondary d-block" style="opacity: 0.5;"></i>
                                <span class="fw-semibold">No shipment leads found matching current filters.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center border-top" style="border-radius: 0 0 12px 12px;">
        <div class="text-muted" style="font-size: 0.75rem;">
            Showing <strong>{{ $leads->firstItem() ?? 0 }}</strong> to <strong>{{ $leads->lastItem() ?? 0 }}</strong> of <strong>{{ $leads->total() }}</strong> leads
        </div>
        <div>
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal: Mark as Not a Lead -->
<div class="modal fade" id="notLeadModal" tabindex="-1" aria-labelledby="notLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #e2e8f0;">
            <form id="notLeadForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header py-3 bg-light" style="border-radius: 12px 12px 0 0;">
                    <h6 class="modal-title fw-bold text-dark" id="notLeadModalLabel">
                        <i class="fa-solid fa-ban text-danger me-2"></i>Mark as Not a Lead
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-dark mb-2">
                        Are you sure you want to mark this email as <strong>Not a Lead</strong>?
                    </p>
                    <div class="p-2 mb-3 rounded-2 bg-light border small text-muted">
                        <div><strong>Customer:</strong> <span id="notLeadCustomer" class="text-dark fw-semibold"></span></div>
                        <div><strong>Email:</strong> <span id="notLeadEmail" class="text-primary"></span></div>
                    </div>
                    <div class="alert alert-warning py-2 px-3 small mb-3" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        This email will be removed from your leads list and will <strong>no longer be counted in Total Inquiries or Waiting for Reply</strong>.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="exclude_domain" id="notLeadExcludeDomain" value="1">
                        <label class="form-check-label small text-dark" for="notLeadExcludeDomain">
                            Also blacklist domain <code id="notLeadDomainName" class="fw-bold text-danger"></code> in <strong>Excluded Domains</strong> so future emails are never counted as leads.
                        </label>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold">
                        <i class="fa-solid fa-ban me-1"></i> Confirm: Not a Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openNotLeadModal(leadId, customerName, customerEmail) {
    const form = document.getElementById('notLeadForm');
    form.action = `/shipment-leads/leads/${leadId}/mark-not-lead`;
    document.getElementById('notLeadCustomer').textContent = customerName;
    document.getElementById('notLeadEmail').textContent = customerEmail;

    let domain = '';
    if (customerEmail && customerEmail.includes('@')) {
        domain = customerEmail.split('@')[1].trim();
    }
    document.getElementById('notLeadDomainName').textContent = domain ? '@' + domain : '';
    document.getElementById('notLeadExcludeDomain').checked = false;

    const modal = new bootstrap.Modal(document.getElementById('notLeadModal'));
    modal.show();
}
</script>
@endsection
