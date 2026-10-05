@extends('shipment_leads.layouts.app')

@section('title', 'Customer-Wise Lead Report')
@section('page_title', 'Customer-Wise Lead Report')

@section('content')
<!-- Header & Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h6 class="m-0 fw-bold text-dark" style="font-size: 1rem;">
            <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Customer-Wise Lead Report
        </h6>
        <p class="text-muted m-0" style="font-size: 0.8rem;">
            Inquiry volume and conversion funnel aggregated per unique customer email address.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; font-weight: 600;">
            <i class="fa-solid fa-table-list me-1"></i> Open All Leads
        </a>
    </div>
</div>

<!-- Funnel Analytics KPI Row: Customers → Leads → Replied → Quotations → Finalized -->
<div class="row g-2 mb-3">
    <!-- Total Unique Customers -->
    <div class="col-xl col-md-4 col-6">
        <div class="card card-modern shadow-sm p-3 h-100" style="border-left: 3.5px solid #0284c7 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Unique Customers</span>
                    <h4 class="fw-bold text-dark m-0 my-1">{{ number_format($totalUniqueCustomers) }}</h4>
                    <span class="text-muted" style="font-size: 0.72rem;">Active client accounts</span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7;">
                    <i class="fa-solid fa-address-book" style="font-size: 1.05rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Inquiries / Leads -->
    <div class="col-xl col-md-4 col-6">
        <div class="card card-modern shadow-sm p-3 h-100" style="border-left: 3.5px solid #3b82f6 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Total Leads</span>
                    <h4 class="fw-bold text-dark m-0 my-1">{{ number_format($totalLeads) }}</h4>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        {{ $totalUniqueCustomers > 0 ? round($totalLeads / $totalUniqueCustomers, 1) : 0 }} avg / customer
                    </span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #dbeafe; color: #2563eb;">
                    <i class="fa-solid fa-boxes-stacked" style="font-size: 1.05rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Replied Leads -->
    <div class="col-xl col-md-4 col-6">
        <div class="card card-modern shadow-sm p-3 h-100" style="border-left: 3.5px solid #10b981 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-success fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Replied Leads</span>
                    <h4 class="fw-bold text-success m-0 my-1">{{ number_format($totalReplied) }}</h4>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        {{ $totalLeads > 0 ? round(($totalReplied / $totalLeads) * 100, 1) : 0 }}% response rate
                    </span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #dcfce7; color: #10b981;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.05rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotation Leads (QGLT) -->
    <div class="col-xl col-md-4 col-6">
        <div class="card card-modern shadow-sm p-3 h-100" style="border-left: 3.5px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-warning fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Quotation Leads</span>
                    <h4 class="fw-bold text-dark m-0 my-1">{{ number_format($totalQuotations) }}</h4>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        {{ $totalLeads > 0 ? round(($totalQuotations / $totalLeads) * 100, 1) : 0 }}% quoted (QGLT)
                    </span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-file-invoice-dollar" style="font-size: 1.05rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Leads (GLT) -->
    <div class="col-xl col-md-4 col-6">
        <div class="card card-modern shadow-sm p-3 h-100" style="border-left: 3.5px solid #8b5cf6 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase fw-bold" style="font-size: 0.68rem; color: #7c3aed; letter-spacing: 0.5px;">Final Leads</span>
                    <h4 class="fw-bold text-dark m-0 my-1">{{ number_format($totalFinal) }}</h4>
                    <span class="text-muted" style="font-size: 0.72rem;">
                        {{ $totalQuotations > 0 ? round(($totalFinal / $totalQuotations) * 100, 1) : 0 }}% win conversion
                    </span>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #ede9fe; color: #7c3aed;">
                    <i class="fa-solid fa-flag-checkered" style="font-size: 1.05rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filtering Card -->
<div class="card card-modern shadow-sm p-3 mb-3">
    <form method="GET" action="{{ route('shipment-leads.customer-reports.index') }}" class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-lg-5 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 0.75rem;"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search customer name, email, company..." value="{{ request('search') }}" style="border-radius: 0 8px 8px 0; font-size: 0.8125rem;">
            </div>
        </div>

        <!-- Stage Filter -->
        <div class="col-lg-3 col-md-3">
            <select name="filter" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                <option value="">All Customers (No Filter)</option>
                <option value="has_quotations" {{ request('filter') === 'has_quotations' ? 'selected' : '' }}>Has Quotation Leads (QGLT &ge; 1)</option>
                <option value="has_final" {{ request('filter') === 'has_final' ? 'selected' : '' }}>Has Final Leads (GLT &ge; 1)</option>
                <option value="has_replied" {{ request('filter') === 'has_replied' ? 'selected' : '' }}>Has Replied Leads</option>
                <option value="unreplied_only" {{ request('filter') === 'unreplied_only' ? 'selected' : '' }}>Has Pending / Unreplied Leads</option>
            </select>
        </div>

        <!-- Sort By -->
        <div class="col-lg-3 col-md-3">
            <select name="sort" class="form-select form-select-sm" style="border-radius: 8px; font-size: 0.8125rem;">
                <option value="most_leads" {{ request('sort', 'most_leads') === 'most_leads' ? 'selected' : '' }}>Sort: Most Leads First</option>
                <option value="most_quotations" {{ request('sort') === 'most_quotations' ? 'selected' : '' }}>Sort: Most Quotations First</option>
                <option value="most_final" {{ request('sort') === 'most_final' ? 'selected' : '' }}>Sort: Most Final Leads First</option>
                <option value="most_replied" {{ request('sort') === 'most_replied' ? 'selected' : '' }}>Sort: Most Replied First</option>
                <option value="latest_date" {{ request('sort') === 'latest_date' ? 'selected' : '' }}>Sort: Most Recent Inquiry</option>
                <option value="oldest_date" {{ request('sort') === 'oldest_date' ? 'selected' : '' }}>Sort: Oldest Inquiry</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Customer Name (A-Z)</option>
            </select>
        </div>

        <!-- Submit & Reset -->
        <div class="col-lg-1 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-2 flex-grow-1" style="border-radius: 8px;" title="Apply Filter">
                <i class="fa-solid fa-filter me-1" style="font-size: 0.75rem;"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'filter', 'sort']))
                <a href="{{ route('shipment-leads.customer-reports.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="border-radius: 8px;" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left" style="font-size: 0.75rem;"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Customer Listing Table Card -->
<div class="card card-modern shadow-sm overflow-hidden mb-4">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark" style="font-size: 0.85rem;">
            <i class="fa-solid fa-table-list text-primary me-1.5"></i>
            Customers List
            <span class="badge bg-light text-primary border ms-1" style="font-size: 0.75rem;">{{ number_format($customers->total()) }} total</span>
        </span>
        <span class="text-muted small" style="font-size: 0.78rem;">
            Showing {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th class="text-center" style="width: 110px;">Total Leads</th>
                    <th class="text-center" style="width: 120px;">Replied Leads</th>
                    <th class="text-center" style="width: 130px;">Quotation Leads</th>
                    <th class="text-center" style="width: 120px;">Final Leads</th>
                    <th style="width: 140px;">Last Inquiry</th>
                    <th class="text-center" style="width: 110px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr>
                        <!-- Number index -->
                        <td class="text-center text-muted fw-semibold">
                            {{ $customers->firstItem() + $index }}
                        </td>

                        <!-- Customer Name -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 32px; height: 32px; min-width: 32px; background: linear-gradient(135deg, #0284c7, #38bdf8); font-size: 0.78rem;">
                                    {{ strtoupper(substr($customer->customer_name ?: ($customer->email ?: 'U'), 0, 1)) }}
                                </div>
                                <div class="text-truncate" style="max-width: 220px;">
                                    <div class="fw-bold text-dark text-truncate" title="{{ $customer->customer_name }}">
                                        {{ $customer->customer_name }}
                                    </div>
                                    @if(!empty($customer->company_name))
                                        <div class="text-muted text-truncate" style="font-size: 0.72rem;" title="{{ $customer->company_name }}">
                                            <i class="fa-regular fa-building me-1"></i>{{ $customer->company_name }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td>
                            <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email]) }}" class="text-decoration-none fw-semibold text-primary" title="View customer report for {{ $customer->email }}">
                                {{ $customer->email }}
                            </a>
                        </td>

                        <!-- Total Leads -->
                        <td class="text-center">
                            <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email, 'tab' => 'leads']) }}" class="badge rounded-pill text-decoration-none px-2.5 py-1" style="background-color: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.8rem; font-weight: 700;">
                                {{ number_format($customer->total_leads) }}
                            </a>
                        </td>

                        <!-- Replied Leads -->
                        <td class="text-center">
                            @if($customer->replied_leads > 0)
                                <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email, 'tab' => 'replied']) }}" class="badge rounded-pill text-decoration-none px-2.5 py-1" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 0.78rem; font-weight: 700;">
                                    <i class="fa-solid fa-circle-check me-1" style="font-size: 0.65rem;"></i>{{ number_format($customer->replied_leads) }}
                                </a>
                            @else
                                <span class="badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">0</span>
                            @endif
                        </td>

                        <!-- Quotation Leads -->
                        <td class="text-center">
                            @if($customer->quotation_leads > 0)
                                <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email, 'tab' => 'quotations']) }}" class="badge rounded-pill text-decoration-none px-2.5 py-1" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.78rem; font-weight: 700;">
                                    <i class="fa-solid fa-file-invoice-dollar me-1" style="font-size: 0.65rem;"></i>{{ number_format($customer->quotation_leads) }}
                                </a>
                            @else
                                <span class="badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">0</span>
                            @endif
                        </td>

                        <!-- Final Leads -->
                        <td class="text-center">
                            @if($customer->final_leads > 0)
                                <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email, 'tab' => 'final']) }}" class="badge rounded-pill text-decoration-none px-2.5 py-1" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-size: 0.78rem; font-weight: 700;">
                                    <i class="fa-solid fa-flag-checkered me-1" style="font-size: 0.65rem;"></i>{{ number_format($customer->final_leads) }}
                                </a>
                            @else
                                <span class="badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">0</span>
                            @endif
                        </td>

                        <!-- Last Inquiry Date -->
                        <td class="text-muted" style="font-size: 0.75rem;">
                            @if($customer->last_inquiry_date)
                                <div><i class="fa-regular fa-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($customer->last_inquiry_date)->timezone('Europe/London')->format('M d, Y H:i') }}</div>
                                <div class="text-secondary" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($customer->last_inquiry_date)->diffForHumans() }}</div>
                            @else
                                -
                            @endif
                        </td>

                        <!-- Action Button -->
                        <td class="text-center">
                            <a href="{{ route('shipment-leads.customer-reports.show', ['email' => $customer->email]) }}" class="btn btn-outline-primary btn-sm px-2.5 py-1" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;" title="View detailed leads for {{ $customer->email }}">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fa-3x mb-2 text-secondary d-block" style="opacity: 0.4;"></i>
                            <span class="fw-semibold">No customers found matching the search criteria or filters.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                Showing page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}
            </span>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
