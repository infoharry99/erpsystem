@extends('shipment_leads.layouts.app')

@section('title', 'Customer Lead Details - ' . ($customerName ?: $email))
@section('page_title', 'Customer Lead Details')

@section('content')
<!-- Back & Header Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shipment-leads.customer-reports.index') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; font-weight: 600;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Customer Report
        </a>
        <span class="text-muted">/</span>
        <span class="fw-bold text-dark" style="font-size: 0.95rem;">Customer Details</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shipment-leads.leads.index', ['search' => $email]) }}" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px; font-weight: 600;">
            <i class="fa-solid fa-filter me-1"></i> Filter in All Leads
        </a>
    </div>
</div>

<!-- Customer Overview Profile Card -->
<div class="card card-modern shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 58px; height: 58px; font-size: 1.4rem; background: linear-gradient(135deg, #0284c7, #38bdf8);">
                    {{ strtoupper(substr($customerName ?: ($email ?: 'U'), 0, 1)) }}
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h5 class="fw-bold text-dark m-0">{{ $customerName ?: 'Unknown Customer' }}</h5>
                    @if($companyName)
                        <span class="badge bg-light text-dark border px-2.5 py-1" style="font-size: 0.78rem;">
                            <i class="fa-regular fa-building me-1 text-secondary"></i>{{ $companyName }}
                        </span>
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted" style="font-size: 0.82rem;">
                    <div>
                        <i class="fa-regular fa-envelope me-1 text-primary"></i>
                        <a href="mailto:{{ $email }}" class="text-decoration-none text-dark fw-semibold">{{ $email }}</a>
                    </div>
                    @if($customerPhone)
                        <div>
                            <i class="fa-solid fa-phone me-1 text-success"></i>
                            <span>{{ $customerPhone }}</span>
                        </div>
                    @endif
                    <div>
                        <i class="fa-regular fa-calendar-check me-1 text-secondary"></i>
                        <span>First Inquiry: <strong>{{ $leads->last()?->received_date ? \Carbon\Carbon::parse($leads->last()->received_date)->timezone('Europe/London')->format('M d, Y') : '-' }}</strong></span>
                    </div>
                    <div>
                        <i class="fa-regular fa-clock me-1 text-secondary"></i>
                        <span>Latest Inquiry: <strong>{{ $leads->first()?->received_date ? \Carbon\Carbon::parse($leads->first()->received_date)->timezone('Europe/London')->format('M d, Y H:i') : '-' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <!-- Funnel Summary Pills -->
        <div class="row g-2 text-center">
            <div class="col-sm-3 col-6">
                <div class="p-2 rounded bg-light border">
                    <div class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Total Leads</div>
                    <div class="fw-bold text-dark fs-5 my-0.5">{{ number_format($leads->count()) }}</div>
                    <span class="text-secondary" style="font-size: 0.7rem;">Inquiries received</span>
                </div>
            </div>
            <div class="col-sm-3 col-6">
                <div class="p-2 rounded border" style="background-color: #ecfdf5; border-color: #a7f3d0 !important;">
                    <div class="text-success fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Replied Leads</div>
                    <div class="fw-bold text-success fs-5 my-0.5">{{ number_format($repliedLeads->count()) }}</div>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $leads->count() > 0 ? round(($repliedLeads->count() / $leads->count()) * 100, 1) : 0 }}% response rate
                    </span>
                </div>
            </div>
            <div class="col-sm-3 col-6">
                <div class="p-2 rounded border" style="background-color: #fef3c7; border-color: #fde68a !important;">
                    <div class="text-warning fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #b45309 !important;">Quotation Sent (QGLT)</div>
                    <div class="fw-bold fs-5 my-0.5" style="color: #b45309;">{{ number_format($quotationLeads->count()) }}</div>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $leads->count() > 0 ? round(($quotationLeads->count() / $leads->count()) * 100, 1) : 0 }}% quoted
                    </span>
                </div>
            </div>
            <div class="col-sm-3 col-6">
                <div class="p-2 rounded border" style="background-color: #ede9fe; border-color: #c4b5fd !important;">
                    <div class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #6d28d9;">Final Leads (GLT)</div>
                    <div class="fw-bold fs-5 my-0.5" style="color: #6d28d9;">{{ number_format($finalLeads->count()) }}</div>
                    <span class="text-muted" style="font-size: 0.7rem;">
                        {{ $leads->count() > 0 ? round(($finalLeads->count() / $leads->count()) * 100, 1) : 0 }}% finalized
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Inquiries Tabs (4 Tabs) -->
<div class="card card-modern shadow-sm">
    <div class="card-header bg-white p-2 p-md-3 border-bottom">
        <ul class="nav nav-pills" id="customerLeadsTabs" role="tablist">
            <!-- Tab 1: All Leads -->
            <li class="nav-item me-2" role="presentation">
                <button class="nav-link {{ $activeTab === 'leads' ? 'active' : '' }}" id="leads-tab" data-bs-toggle="pill" data-bs-target="#tab-leads" type="button" role="tab" aria-controls="tab-leads" aria-selected="{{ $activeTab === 'leads' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-boxes-stacked me-1.5"></i> Leads
                    <span class="badge rounded-pill ms-1.5 {{ $activeTab === 'leads' ? 'bg-white text-primary' : 'bg-light text-secondary border' }}">
                        {{ $leads->count() }}
                    </span>
                </button>
            </li>

            <!-- Tab 2: Replied Leads -->
            <li class="nav-item me-2" role="presentation">
                <button class="nav-link {{ $activeTab === 'replied' ? 'active' : '' }}" id="replied-tab" data-bs-toggle="pill" data-bs-target="#tab-replied" type="button" role="tab" aria-controls="tab-replied" aria-selected="{{ $activeTab === 'replied' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-circle-check me-1.5 text-success"></i> Replied Leads
                    <span class="badge rounded-pill ms-1.5 {{ $activeTab === 'replied' ? 'bg-white text-success' : 'bg-light text-secondary border' }}">
                        {{ $repliedLeads->count() }}
                    </span>
                </button>
            </li>

            <!-- Tab 3: Quotation Leads -->
            <li class="nav-item me-2" role="presentation">
                <button class="nav-link {{ $activeTab === 'quotations' ? 'active' : '' }}" id="quotations-tab" data-bs-toggle="pill" data-bs-target="#tab-quotations" type="button" role="tab" aria-controls="tab-quotations" aria-selected="{{ $activeTab === 'quotations' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-file-invoice-dollar me-1.5 text-warning"></i> Quotation Leads
                    <span class="badge rounded-pill ms-1.5 {{ $activeTab === 'quotations' ? 'bg-white text-warning' : 'bg-light text-secondary border' }}">
                        {{ $quotationLeads->count() }}
                    </span>
                </button>
            </li>

            <!-- Tab 4: Final Leads -->
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'final' ? 'active' : '' }}" id="final-tab" data-bs-toggle="pill" data-bs-target="#tab-final" type="button" role="tab" aria-controls="tab-final" aria-selected="{{ $activeTab === 'final' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-flag-checkered me-1.5" style="color: #8b5cf6;"></i> Final Leads
                    <span class="badge rounded-pill ms-1.5 {{ $activeTab === 'final' ? 'bg-white text-purple' : 'bg-light text-secondary border' }}">
                        {{ $finalLeads->count() }}
                    </span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="customerLeadsTabsContent">
        <!-- 1. All Leads Tab Pane -->
        <div class="tab-pane fade {{ $activeTab === 'leads' ? 'show active' : '' }}" id="tab-leads" role="tabpanel" aria-labelledby="leads-tab">
            @include('shipment_leads.customer_reports._leads_table', ['tabLeads' => $leads, 'tabTitle' => 'All Inquiries'])
        </div>

        <!-- 2. Replied Leads Tab Pane -->
        <div class="tab-pane fade {{ $activeTab === 'replied' ? 'show active' : '' }}" id="tab-replied" role="tabpanel" aria-labelledby="replied-tab">
            @include('shipment_leads.customer_reports._leads_table', ['tabLeads' => $repliedLeads, 'tabTitle' => 'Replied Inquiries'])
        </div>

        <!-- 3. Quotation Leads Tab Pane -->
        <div class="tab-pane fade {{ $activeTab === 'quotations' ? 'show active' : '' }}" id="tab-quotations" role="tabpanel" aria-labelledby="quotations-tab">
            @include('shipment_leads.customer_reports._leads_table', ['tabLeads' => $quotationLeads, 'tabTitle' => 'Quotation Sent Inquiries (QGLT)'])
        </div>

        <!-- 4. Final Leads Tab Pane -->
        <div class="tab-pane fade {{ $activeTab === 'final' ? 'show active' : '' }}" id="tab-final" role="tabpanel" aria-labelledby="final-tab">
            @include('shipment_leads.customer_reports._leads_table', ['tabLeads' => $finalLeads, 'tabTitle' => 'Final Leads (QGLT + GLT)'])
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #customerLeadsTabs .nav-link {
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        color: #475569;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    #customerLeadsTabs .nav-link:hover {
        background-color: #f1f5f9;
        color: #0284c7;
    }
    #customerLeadsTabs .nav-link.active {
        background-color: #0284c7;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    }
    #customerLeadsTabs .nav-link.active i {
        color: #ffffff !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Keep tab state synchronized with URL hash / history
    document.addEventListener('DOMContentLoaded', function () {
        var triggerTabList = [].slice.call(document.querySelectorAll('#customerLeadsTabs button[data-bs-toggle="pill"]'));
        triggerTabList.forEach(function (triggerEl) {
            triggerEl.addEventListener('shown.bs.tab', function (event) {
                var targetId = event.target.getAttribute('data-bs-target').replace('#tab-', '');
                if (history.pushState) {
                    var newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('tab', targetId);
                    window.history.replaceState({path: newUrl.href}, '', newUrl.href);
                }
            });
        });
    });
</script>
@endpush
