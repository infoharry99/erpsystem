@extends('shipment_leads.layouts.app')

@section('title', 'Shipment Leads Management')
@section('page_title', 'Shipment Leads')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('shipment-leads.leads.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search customer, subject, lead ID..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="email_account_id" class="form-select">
                    <option value="">-- All Mailboxes --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('email_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="reply_status" class="form-select">
                    <option value="">-- Reply Status --</option>
                    <option value="not_replied" {{ request('reply_status') === 'not_replied' ? 'selected' : '' }}>🔴 Not Replied</option>
                    <option value="replied" {{ request('reply_status') === 'replied' ? 'selected' : '' }}>🟢 Replied</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="lead_status" class="form-select">
                    <option value="">-- Lead Status --</option>
                    @foreach(['new', 'not_replied', 'replied', 'follow_up', 'quotation_sent', 'negotiation', 'booked', 'won', 'lost', 'spam', 'closed'] as $st)
                        <option value="{{ $st }}" {{ request('lead_status') === $st ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="shipment_type" class="form-select">
                    <option value="">-- Shipment Type --</option>
                    <option value="sea_fcl" {{ request('shipment_type') === 'sea_fcl' ? 'selected' : '' }}>Sea FCL</option>
                    <option value="sea_lcl" {{ request('shipment_type') === 'sea_lcl' ? 'selected' : '' }}>Sea LCL</option>
                    <option value="air_freight" {{ request('shipment_type') === 'air_freight' ? 'selected' : '' }}>Air Freight</option>
                    <option value="road_freight" {{ request('shipment_type') === 'road_freight' ? 'selected' : '' }}>Road Freight</option>
                    <option value="reefer" {{ request('shipment_type') === 'reefer' ? 'selected' : '' }}>Reefer</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="m-0 font-weight-bold text-dark">
            <i class="fa-solid fa-boxes-packing me-2"></i> All Shipment Leads ({{ $leads->total() }})
        </h5>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">Sort By:</small>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="btn btn-sm {{ request('sort', 'newest') === 'newest' ? 'btn-dark' : 'btn-outline-secondary' }}">Newest</a>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'not_replied']) }}" class="btn btn-sm {{ request('sort') === 'not_replied' ? 'btn-danger' : 'btn-outline-secondary' }}">Not Replied First</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Lead ID</th>
                        <th>Received Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Origin $\rightarrow$ Destination</th>
                        <th>Shipment Type</th>
                        <th>Source Mailbox</th>
                        <th>Reply Status</th>
                        <th>Lead Status</th>
                        <th>Assigned To</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr class="{{ $lead->reply_status === 'not_replied' ? 'table-warning' : '' }}">
                            <td><strong>#{{ $lead->id }}</strong></td>
                            <td><small class="text-muted">{{ $lead->received_date ? $lead->received_date->format('M d, Y H:i') : '-' }}</small></td>
                            <td>
                                <strong>{{ $lead->customer_name }}</strong><br>
                                <small class="text-muted">{{ $lead->customer_email }}</small>
                            </td>
                            <td>
                                <strong class="d-inline-block text-truncate" style="max-width: 250px;">{{ $lead->email_subject }}</strong>
                                @if($lead->ai_summary)
                                    <br><small class="text-secondary d-inline-block text-truncate" style="max-width: 280px;" title="{{ $lead->ai_summary }}"><i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i> {{ $lead->ai_summary }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $lead->origin ?: 'TBD' }}</span>
                                <i class="fa-solid fa-arrow-right-long text-muted mx-1"></i>
                                <span class="badge bg-light text-dark border">{{ $lead->destination ?: 'TBD' }}</span>
                            </td>
                            <td><span class="badge bg-info text-dark">{{ $lead->shipment_type_label }}</span></td>
                            <td><small class="text-muted">{{ $lead->account->email ?? 'N/A' }}</small></td>
                            <td>
                                @if($lead->reply_status === 'replied')
                                    <span class="badge badge-replied"><i class="fa-solid fa-circle-check me-1"></i> Replied</span>
                                @else
                                    <span class="badge badge-not-replied"><i class="fa-solid fa-circle-exclamation me-1"></i> Not Replied</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $lead->lead_status) }}</span>
                            </td>
                            <td><small>{{ $lead->assignedUser->name ?? 'Unassigned' }}</small></td>
                            <td>
                                <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-folder-open me-1"></i> Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                No shipment leads match the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div class="small text-muted mb-2 mb-md-0">
            Showing <strong>{{ $leads->firstItem() ?? 0 }}</strong> to <strong>{{ $leads->lastItem() ?? 0 }}</strong> of <strong>{{ $leads->total() }}</strong> leads
        </div>
        <div>
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
