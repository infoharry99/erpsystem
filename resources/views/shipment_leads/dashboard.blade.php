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
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'quotation_sent']) }}" class="text-decoration-none">
            <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            Quotations Sent (QGLT)
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
        </a>
    </div>
</div>

<!-- Row 2: Secondary Pipeline Metrics -->
<div class="row g-2 mb-3">
    <div class="col-xl-2 col-md-4 col-6">
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
    <div class="col-xl-2 col-md-4 col-6">
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'new']) }}" class="text-decoration-none">
            <div class="card card-modern shadow-sm p-2 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-primary text-uppercase fw-semibold" style="font-size: 0.68rem;">New Leads</span>
                        <h5 class="m-0 fw-bold text-primary" style="font-size: 1.1rem;">{{ number_format($newLeadsCount) }}</h5>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7; font-size: 0.8rem;">
                        <i class="fa-solid fa-sparkles"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'final_lead']) }}" class="text-decoration-none">
            <div class="card card-modern shadow-sm p-2 px-3" style="border-left: 3px solid #8b5cf6 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; color: #7c3aed;">Final Leads</span>
                        <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($finalLeadsCount) }}</h5>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #ede9fe; color: #7c3aed; font-size: 0.8rem;">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'booked']) }}" class="text-decoration-none">
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
        </a>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'won']) }}" class="text-decoration-none">
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
        </a>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'lost']) }}" class="text-decoration-none">
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
        </a>
    </div>
</div>

<!-- Interactive Lead Conversion Funnel (Pipeline Velocity) -->
<div class="card card-modern shadow-sm mb-3 p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2.5">
        <div>
            <h6 class="m-0 fw-bold text-dark" style="font-size: 0.88rem;">
                <i class="fa-solid fa-arrows-split-up-and-left text-primary me-1.5"></i> Lead Conversion Funnel (Pipeline Velocity)
            </h6>
            <span class="text-muted" style="font-size: 0.72rem;">Progression tracking from initial inquiry intake to quotation (QGLT) and finalized deals (GLT)</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">UK London Time</span>
        </div>
    </div>

    <!-- Funnel Pipeline Steps -->
    <div class="row g-2 align-items-center text-center">
        <!-- Step 1: Total Intake -->
        <div class="col-md-3 col-6">
            <a href="{{ route('shipment-leads.leads.index') }}" class="text-decoration-none">
                <div class="p-2.5 rounded-3 border h-100 transition-all hover-shadow" style="background: #f0f9ff; border-color: #bae6fd !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white text-primary border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">STAGE 1</span>
                        <i class="fa-solid fa-boxes-stacked text-primary" style="font-size: 0.85rem;"></i>
                    </div>
                    <div class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Total Intake</div>
                    <h4 class="fw-bold text-dark my-1">{{ number_format($totalLeads) }}</h4>
                    <span class="text-muted" style="font-size: 0.7rem;">100% of all inquiries</span>
                </div>
            </a>
        </div>

        <!-- Step 2: Replied Inquiries -->
        <div class="col-md-3 col-6">
            <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'replied']) }}" class="text-decoration-none">
                <div class="p-2.5 rounded-3 border h-100 transition-all hover-shadow" style="background: #ecfdf5; border-color: #a7f3d0 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white text-success border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">STAGE 2</span>
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 0.85rem;"></i>
                    </div>
                    <div class="text-success fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Replied Leads</div>
                    <h4 class="fw-bold text-success my-1">{{ number_format($repliedCount + $quotationsSent + $finalLeadsCount) }}</h4>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $totalLeads > 0 ? round((($repliedCount + $quotationsSent + $finalLeadsCount) / $totalLeads) * 100, 1) : 0 }}% response rate
                    </span>
                </div>
            </a>
        </div>

        <!-- Step 3: Quotations Sent (QGLT) -->
        <div class="col-md-3 col-6">
            <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'quotation_sent']) }}" class="text-decoration-none">
                <div class="p-2.5 rounded-3 border h-100 transition-all hover-shadow" style="background: #fef3c7; border-color: #fde68a !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white text-warning border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700; color: #b45309 !important;">STAGE 3</span>
                        <i class="fa-solid fa-file-invoice-dollar text-warning" style="font-size: 0.85rem;"></i>
                    </div>
                    <div class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #b45309;">Quotation Sent (QGLT)</div>
                    <h4 class="fw-bold my-1" style="color: #b45309;">{{ number_format($quotationsSent) }}</h4>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $totalLeads > 0 ? round(($quotationsSent / $totalLeads) * 100, 1) : 0 }}% of inquiries
                    </span>
                </div>
            </a>
        </div>

        <!-- Step 4: Final Leads (GLT) -->
        <div class="col-md-3 col-6">
            <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'final_lead']) }}" class="text-decoration-none">
                <div class="p-2.5 rounded-3 border h-100 transition-all hover-shadow" style="background: #ede9fe; border-color: #c4b5fd !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700; color: #6d28d9 !important;">STAGE 4</span>
                        <i class="fa-solid fa-flag-checkered" style="font-size: 0.85rem; color: #6d28d9;"></i>
                    </div>
                    <div class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #6d28d9;">Final Leads (GLT)</div>
                    <h4 class="fw-bold my-1" style="color: #6d28d9;">{{ number_format($finalLeadsCount) }}</h4>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $totalLeads > 0 ? round(($finalLeadsCount / $totalLeads) * 100, 1) : 0 }}% conversion
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Row 3: Visual Analytics Charts -->
<div class="row g-3 mb-3">
    <!-- Trend Chart -->
    <div class="col-lg-8">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between py-2.5 px-3 border-bottom">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-chart-line text-primary me-1.5"></i> Inquiry Intake vs. Response Trend (Last 14 Days)
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Comparison of incoming leads vs sales replies sent</span>
                </div>
                <div class="d-flex align-items-center gap-1 mt-1 mt-sm-0" id="trendChartToggle">
                    <button type="button" class="btn btn-outline-secondary active btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="toggleTrendSeries('all', this)">All</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="toggleTrendSeries('intake', this)">Inquiries</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="toggleTrendSeries('replies', this)">Replies</button>
                </div>
            </div>
            <div class="card-body p-3">
                <div style="height: 250px; position: relative;">
                    <canvas id="chartLeadsByDay"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage Conversion Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card card-modern shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                <div>
                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-chart-pie text-primary me-1.5"></i> Stage Conversion Distribution
                    </span>
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Lead distribution across operational stages</span>
                </div>
            </div>
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-between">
                <div style="height: 180px; width: 100%; position: relative;">
                    <canvas id="chartLeadStageConversion"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                        <div class="fw-bold text-dark fs-4 m-0" style="line-height: 1;">{{ number_format($totalLeads) }}</div>
                        <span class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Leads</span>
                    </div>
                </div>

                <!-- Custom Interactive Legend Breakdown -->
                <div class="w-100 mt-2.5 d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                        <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #0284c7;"></span> New Inquiries</span>
                        <span class="text-muted fw-bold">{{ number_format($newLeadsCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($newLeadsCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                        <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #ef4444;"></span> Waiting Reply</span>
                        <span class="text-danger fw-bold">{{ number_format($notRepliedCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($notRepliedCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                        <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #10b981;"></span> Replied</span>
                        <span class="text-success fw-bold">{{ number_format($repliedCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($repliedCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                        <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #f59e0b;"></span> Quotation Sent (QGLT)</span>
                        <span class="text-warning fw-bold" style="color: #b45309 !important;">{{ number_format($quotationsSent) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($quotationsSent / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                        <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #8b5cf6;"></span> Final Leads (GLT)</span>
                        <span class="fw-bold" style="color: #6d28d9;">{{ number_format($finalLeadsCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($finalLeadsCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                    </div>
                </div>
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

    // 1. Leads by Day Trend Chart (Dual Series: Inquiries Intake vs. Replies Sent)
    const ctxTrend = document.getElementById('chartLeadsByDay').getContext('2d');
    const gradBlue = ctxTrend.createLinearGradient(0, 0, 0, 240);
    gradBlue.addColorStop(0, 'rgba(2, 132, 199, 0.28)');
    gradBlue.addColorStop(1, 'rgba(2, 132, 199, 0.01)');

    const gradGreen = ctxTrend.createLinearGradient(0, 0, 0, 240);
    gradGreen.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
    gradGreen.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

    window.leadsTrendChart = new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($dates) !!},
            datasets: [
                {
                    label: 'Inquiries Intake',
                    data: {!! json_encode($leadsByDayCounts) !!},
                    borderColor: '#0284c7',
                    backgroundColor: gradBlue,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0284c7',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                },
                {
                    label: 'Replies Sent',
                    data: {!! json_encode($repliedByDayCounts) !!},
                    borderColor: '#10b981',
                    backgroundColor: gradGreen,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 12,
                        font: { size: 11, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    usePointStyle: true
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 10 } },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });

    window.toggleTrendSeries = function(type, btn) {
        if (!window.leadsTrendChart) return;
        document.querySelectorAll('#trendChartToggle .btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (type === 'all') {
            window.leadsTrendChart.setDatasetVisibility(0, true);
            window.leadsTrendChart.setDatasetVisibility(1, true);
        } else if (type === 'intake') {
            window.leadsTrendChart.setDatasetVisibility(0, true);
            window.leadsTrendChart.setDatasetVisibility(1, false);
        } else if (type === 'replies') {
            window.leadsTrendChart.setDatasetVisibility(0, false);
            window.leadsTrendChart.setDatasetVisibility(1, true);
        }
        window.leadsTrendChart.update();
    };

    // 2. Stage Conversion Distribution Doughnut Chart
    const ctxStage = document.getElementById('chartLeadStageConversion').getContext('2d');
    new Chart(ctxStage, {
        type: 'doughnut',
        data: {
            labels: ['New Inquiries', 'Waiting Reply', 'Replied', 'Quotation Sent (QGLT)', 'Final Leads (GLT)'],
            datasets: [{
                data: [
                    {{ (int)$newLeadsCount }},
                    {{ (int)$notRepliedCount }},
                    {{ (int)$repliedCount }},
                    {{ (int)$quotationsSent }},
                    {{ (int)$finalLeadsCount }}
                ],
                backgroundColor: ['#0284c7', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const val = context.parsed;
                            const pct = total > 0 ? Math.round((val / total) * 1000) / 10 : 0;
                            return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                        }
                    }
                }
            },
            cutout: '72%'
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

