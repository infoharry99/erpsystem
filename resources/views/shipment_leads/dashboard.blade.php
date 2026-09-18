@extends('shipment_leads.layouts.app')

@section('title', 'Shipment Sales Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Dashboard Header Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.95rem;">
            Shipment Sales & Lead Analytics
        </h6>
        <p class="text-muted m-0" style="font-size: 0.78rem;">
            Real-time overview of inquiry volume, response rates, and freight mode distribution.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'not_replied']) }}" class="btn btn-outline-danger btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> Waiting For Reply ({{ number_format($notRepliedCount) }})
        </a>
        <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
            <i class="fa-solid fa-table-list me-1"></i> Open All Leads
        </a>
    </div>
</div>

<!-- Row 1: Key Performance Metrics -->
<div class="row g-3 mb-3">
    <!-- Total Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-modern shadow-sm h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Total Inquiries
                    </span>
                    <h3 class="fw-bold text-dark m-0 my-1" style="font-size: 1.75rem;">
                        {{ number_format($totalLeads) }}
                    </h3>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <span class="badge bg-light text-primary border me-1" style="font-size: 0.7rem; font-weight: 600;">
                            +{{ number_format($newToday) }} today
                        </span>
                        <span>{{ number_format($thisWeek) }} this week</span>
                    </div>
                </div>
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-boxes-stacked" style="font-size: 1.15rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Waiting For Reply -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #ef4444 !important;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-uppercase text-danger fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Waiting For Reply
                    </span>
                    <h3 class="fw-bold text-danger m-0 my-1" style="font-size: 1.75rem;">
                        {{ number_format($notRepliedCount) }}
                    </h3>
                    <div style="font-size: 0.75rem;">
                        <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'not_replied']) }}" class="text-danger text-decoration-none fw-semibold">
                            Requires sales response <i class="fa-solid fa-arrow-right ms-0.5" style="font-size: 0.65rem;"></i>
                        </a>
                    </div>
                </div>
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fee2e2; color: #ef4444;">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.15rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Replied Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #10b981 !important;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-uppercase text-success fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Replied Inquiries
                    </span>
                    <h3 class="fw-bold text-success m-0 my-1" style="font-size: 1.75rem;">
                        {{ number_format($repliedCount) }}
                    </h3>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        @if($totalLeads > 0)
                            <span class="badge bg-light text-success border me-1" style="font-size: 0.7rem; font-weight: 600;">
                                {{ round(($repliedCount / $totalLeads) * 100, 1) }}%
                            </span>
                            <span>response rate</span>
                        @else
                            <span>No inquiries yet</span>
                        @endif
                    </div>
                </div>
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #dcfce7; color: #10b981;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotations Sent -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #f59e0b !important;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Quotations Sent
                    </span>
                    <h3 class="fw-bold text-dark m-0 my-1" style="font-size: 1.75rem;">
                        {{ number_format($quotationsSent) }}
                    </h3>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <span>In pricing & quotation stage</span>
                    </div>
                </div>
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-file-invoice-dollar" style="font-size: 1.15rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Secondary Pipeline Metrics -->
<div class="row g-2 mb-3">
    <div class="col-md-3 col-6">
        <div class="card card-modern shadow-sm p-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Today's Intake</span>
                    <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($newToday) }}</h5>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7; font-size: 0.8rem;">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-modern shadow-sm p-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Booked Shipments</span>
                    <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($bookedCount) }}</h5>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f3e8ff; color: #8b5cf6; font-size: 0.8rem;">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-modern shadow-sm p-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Won Deals</span>
                    <h5 class="m-0 fw-bold text-success" style="font-size: 1.1rem;">{{ number_format($wonCount) }}</h5>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #ecfdf5; color: #059669; font-size: 0.8rem;">
                    <i class="fa-solid fa-trophy"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-modern shadow-sm p-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Lost / Closed</span>
                    <h5 class="m-0 fw-bold text-secondary" style="font-size: 1.1rem;">{{ number_format($lostCount) }}</h5>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f1f5f9; color: #64748b; font-size: 0.8rem;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Visual Analytics Charts -->
<div class="row g-3 mb-3">
    <!-- Trend Chart -->
    <div class="col-lg-8">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-chart-line text-primary me-1.5"></i> Lead Volume Trend (Last 14 Days)
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Daily freight inquiries received across connected accounts</span>
                </div>
            </div>
            <div class="card-body p-3">
                <canvas id="chartLeadsByDay" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-chart-pie text-warning me-1.5"></i> Status Breakdown
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Lead distribution across pipeline stages</span>
                </div>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <canvas id="chartLeadStatus" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Mailbox & Shipment Type Breakdown -->
<div class="row g-3 mb-3">
    <!-- Mailbox Distribution -->
    <div class="col-lg-6">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-inbox text-info me-1.5"></i> Leads by Mailbox
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Volume per monitored email account</span>
                </div>
            </div>
            <div class="card-body p-3">
                <canvas id="chartMailboxes" height="140"></canvas>
            </div>
        </div>
    </div>

    <!-- Shipment Type Distribution -->
    <div class="col-lg-6">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-plane-departure text-purple me-1.5" style="color: #8b5cf6;"></i> Shipment Modes & Equipment
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Air Freight, Sea FCL, Sea LCL, Reefer, Road</span>
                </div>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <canvas id="chartShipmentTypes" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Navigation Banner -->
<div class="card card-modern shadow-sm p-3 bg-white" style="border-radius: 12px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #e0f2fe; color: #0284c7;">
                <i class="fa-solid fa-list-check" style="font-size: 0.95rem;"></i>
            </div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.875rem;">Manage Shipment Leads</div>
                <div class="text-muted" style="font-size: 0.75rem;">Search, filter by Gmail read/unread state, assign team reps, and track replies.</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
                Go to Leads Workspace <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Global Font Settings for Chart.js
    Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = "#64748b";

    // 1. Leads by Day Trend Chart
    new Chart(document.getElementById('chartLeadsByDay'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dates) !!},
            datasets: [{
                label: 'Inquiries Received',
                data: {!! json_encode($leadsByDayCounts) !!},
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#0284c7',
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });

    // 2. Status Breakdown Doughnut Chart
    new Chart(document.getElementById('chartLeadStatus'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusCounts)) !!},
                backgroundColor: [
                    '#0284c7', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
                    '#06b6d4', '#ec4899', '#14b8a6', '#64748b', '#94a3b8', '#cbd5e1'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        padding: 8,
                        font: { size: 10 }
                    }
                }
            },
            cutout: '68%'
        }
    });

    // 3. Mailbox Breakdown Horizontal Bar
    new Chart(document.getElementById('chartMailboxes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($mailboxData)) !!},
            datasets: [{
                label: 'Inquiries Received',
                data: {!! json_encode(array_values($mailboxData)) !!},
                backgroundColor: '#0284c7',
                borderRadius: 6,
                maxBarThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: '#f1f5f9' },
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });

    // 4. Shipment Type Distribution
    new Chart(document.getElementById('chartShipmentTypes'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($shipmentTypeCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($shipmentTypeCounts)) !!},
                backgroundColor: ['#0284c7', '#06b6d4', '#8b5cf6', '#10b981', '#f59e0b', '#94a3b8'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        padding: 8,
                        font: { size: 10 }
                    }
                }
            },
            cutout: '60%'
        }
    });
</script>
@endpush

