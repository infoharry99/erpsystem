@extends('shipment_leads.layouts.app')

@section('title', 'Shipment Sales Dashboard')
@section('page_title', 'Shipment Sales Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase mb-1">Total Leads</h6>
                    <h2 class="m-0 font-weight-bold">{{ number_format($totalLeads) }}</h2>
                </div>
                <i class="fa-solid fa-boxes-packing fa-2x text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat bg-danger text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase mb-1">Waiting For Reply</h6>
                    <h2 class="m-0 font-weight-bold">{{ number_format($notRepliedCount) }}</h2>
                </div>
                <i class="fa-solid fa-clock fa-2x text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat bg-success text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 text-uppercase mb-1">Replied Leads</h6>
                    <h2 class="m-0 font-weight-bold">{{ number_format($repliedCount) }}</h2>
                </div>
                <i class="fa-solid fa-reply-all fa-2x text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-stat bg-warning text-dark p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-dark-50 text-uppercase mb-1">Quotations Sent</h6>
                    <h2 class="m-0 font-weight-bold">{{ number_format($quotationsSent) }}</h2>
                </div>
                <i class="fa-solid fa-file-invoice-dollar fa-2x text-dark-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase">New Today</small>
                    <h4 class="m-0 text-dark font-weight-bold">{{ number_format($newToday) }}</h4>
                </div>
                <span class="badge bg-info p-2"><i class="fa-solid fa-calendar-day"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase">Booked Shipments</small>
                    <h4 class="m-0 text-dark font-weight-bold">{{ number_format($bookedCount) }}</h4>
                </div>
                <span class="badge bg-purple p-2 text-white" style="background:#8b5cf6"><i class="fa-solid fa-truck-fast"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase">Won Deals</small>
                    <h4 class="m-0 text-success font-weight-bold">{{ number_format($wonCount) }}</h4>
                </div>
                <span class="badge bg-success p-2"><i class="fa-solid fa-trophy"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted text-uppercase">Lost Deals</small>
                    <h4 class="m-0 text-secondary font-weight-bold">{{ number_format($lostCount) }}</h4>
                </div>
                <span class="badge bg-secondary p-2"><i class="fa-solid fa-circle-xmark"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="m-0 font-weight-bold text-danger">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> Leads Waiting For Reply (Oldest First)
        </h5>
        <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'not_replied']) }}" class="btn btn-outline-danger btn-sm">
            View All Unreplied <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Lead ID</th>
                        <th>Waiting Duration</th>
                        <th>Customer / Subject</th>
                        <th>Route</th>
                        <th>Shipment Type</th>
                        <th>Source Mailbox</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unrepliedLeads as $lead)
                        <tr>
                            <td><strong>#{{ $lead->id }}</strong></td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fa-regular fa-clock me-1"></i> {{ $lead->waiting_duration }}
                                </span>
                            </td>
                            <td>
                                <div><strong>{{ $lead->customer_name }}</strong> <small class="text-muted">({{ $lead->customer_email }})</small></div>
                                <div class="text-truncate small text-secondary" style="max-width: 320px;">{{ $lead->email_subject }}</div>
                            </td>
                            <td>
                                <span class="text-dark">{{ $lead->origin ?: 'TBD' }}</span>
                                <i class="fa-solid fa-arrow-right-long text-muted mx-1"></i>
                                <span class="text-dark">{{ $lead->destination ?: 'TBD' }}</span>
                            </td>
                            <td><span class="badge bg-secondary">{{ $lead->shipment_type_label }}</span></td>
                            <td><small class="text-muted">{{ $lead->account->email ?? 'N/A' }}</small></td>
                            <td>
                                <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="btn btn-sm btn-primary">
                                    Open Lead
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check text-success fa-2x mb-2 d-block"></i>
                                Great job! No unreplied leads waiting.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i> Lead Volume Trend (Last 14 Days)</h6>
            <canvas id="chartLeadsByDay" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fa-solid fa-chart-pie text-warning me-2"></i> Lead Status Breakdown</h6>
            <canvas id="chartLeadStatus" height="240"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fa-solid fa-inbox text-info me-2"></i> Leads by Source Mailbox</h6>
            <canvas id="chartMailboxes" height="140"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fa-solid fa-plane-departure text-purple me-2"></i> Shipment Type Distribution</h6>
            <canvas id="chartShipmentTypes" height="140"></canvas>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="m-0 font-weight-bold text-dark">
            <i class="fa-solid fa-clock-rotate-left me-2"></i> Recent Shipment Leads
        </h5>
        <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-sm btn-outline-primary">
            View All Leads <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Origin $\rightarrow$ Destination</th>
                        <th>Reply Status</th>
                        <th>Lead Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLeads as $lead)
                        <tr>
                            <td><small class="text-muted">{{ $lead->received_date ? $lead->received_date->format('M d, H:i') : '-' }}</small></td>
                            <td>
                                <strong>{{ $lead->customer_name }}</strong><br>
                                <small class="text-muted">{{ $lead->customer_email }}</small>
                            </td>
                            <td><span class="d-inline-block text-truncate" style="max-width: 250px;">{{ $lead->email_subject }}</span></td>
                            <td>
                                <small class="fw-bold">{{ $lead->origin ?: 'TBD' }}</small>
                                <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
                                <small class="fw-bold">{{ $lead->destination ?: 'TBD' }}</small>
                            </td>
                            <td>
                                @if($lead->reply_status === 'replied')
                                    <span class="badge badge-replied"><i class="fa-solid fa-check me-1"></i> Replied</span>
                                @else
                                    <span class="badge badge-not-replied"><i class="fa-solid fa-xmark me-1"></i> Not Replied</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $lead->lead_status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="btn btn-sm btn-outline-dark">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No shipment leads received yet. Click "Refresh Emails" to sync mailboxes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('chartLeadsByDay'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dates) !!},
            datasets: [{
                label: 'Inquiries Received',
                data: {!! json_encode($leadsByDayCounts) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });

    new Chart(document.getElementById('chartLeadStatus'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusCounts)) !!},
                backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6', '#64748b']
            }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('chartMailboxes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($mailboxData)) !!},
            datasets: [{
                label: 'Leads Received',
                data: {!! json_encode(array_values($mailboxData)) !!},
                backgroundColor: '#06b6d4'
            }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('chartShipmentTypes'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($shipmentTypeCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($shipmentTypeCounts)) !!},
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#94a3b8']
            }]
        },
        options: { responsive: true }
    });
</script>
@endpush
