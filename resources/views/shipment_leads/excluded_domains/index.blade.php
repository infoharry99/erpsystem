@extends('shipment_leads.layouts.app')

@section('title', 'Excluded Domains - Shipment CRM')
@section('page_title', 'Excluded Domains')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold text-dark m-0" style="font-size: 1.1rem;">
                <i class="fa-solid fa-ban text-danger me-2"></i>Excluded Domains (Lead Blacklist)
            </h5>
            <p class="text-muted m-0" style="font-size: 0.78rem;">
                Emails from these domains (e.g. <code>mesk.com</code>, shipping lines, newsletters) will be completely skipped and <strong>not counted or created as leads</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('shipment-leads.excluded-domains.prune') }}" method="POST" onsubmit="return confirm('Scan and remove any existing leads that belong to excluded domains or non-inquiry senders?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Scan and delete existing leads from excluded domains">
                    <i class="fa-solid fa-broom me-1"></i> Clean / Prune Existing Leads
                </button>
            </form>
        </div>
    </div>

    <!-- Add Excluded Domain Card -->
    <div class="card card-modern shadow-sm p-3 mb-4" style="background: #ffffff; border-left: 4px solid var(--gt-primary);">
        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
            <i class="fa-solid fa-plus-circle text-primary me-1"></i> Add Domain to Exclusion List
        </h6>
        <form action="{{ route('shipment-leads.excluded-domains.store') }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted fw-semibold small mb-1">Domain Name <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-globe text-secondary"></i></span>
                        <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror" placeholder="e.g. mesk.com or maersk.com" value="{{ old('domain') }}" required>
                    </div>
                    @error('domain')
                        <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold small mb-1">Description / Note (Optional)</label>
                    <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Shipping line tracking, automated announcements" value="{{ old('description') }}">
                </div>
                <div class="col-md-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="prune_existing" id="pruneExisting" value="1" checked>
                        <label class="form-check-label text-muted small" for="pruneExisting" style="font-size: 0.75rem;">
                            Auto-delete existing leads from this domain
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="fa-solid fa-ban me-1"></i> Exclude Domain
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Excluded Domains Listing -->
    <div class="card card-modern shadow-sm">
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold text-dark m-0" style="font-size: 0.9rem;">
                    Active Domain Filter Rules ({{ $excludedDomains->total() }})
                </h6>
            </div>
            <div>
                <form action="{{ route('shipment-leads.excluded-domains.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 260px;">
                        <input type="text" name="search" class="form-control" placeholder="Search domain..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('shipment-leads.excluded-domains.index') }}" class="btn btn-outline-secondary" title="Clear Search">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-light border"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" style="font-size: 0.8125rem;">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3" style="width: 25%;">Domain</th>
                        <th style="width: 30%;">Description / Purpose</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Added On</th>
                        <th class="text-end pe-3" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($excludedDomains as $item)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fee2e2; color: #ef4444; font-size: 0.75rem;">
                                        <i class="fa-solid fa-ban"></i>
                                    </span>
                                    <span>
                                        {{ $item->domain }}
                                        <div class="text-muted fw-normal" style="font-size: 0.7rem;">
                                            Includes all subdomains (<code>*.{{ $item->domain }}</code>)
                                        </div>
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if(!empty($item->description))
                                    <span class="text-dark">{{ $item->description }}</span>
                                @else
                                    <span class="text-muted fst-italic">No note provided</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Active (Skipping)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-circle-pause me-1"></i> Paused
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 0.75rem;">
                                {{ $item->created_at ? $item->created_at->format('M d, Y') : '-' }}
                                @if($item->creator)
                                    <div style="font-size: 0.68rem;">by {{ $item->creator->name }}</div>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <form action="{{ route('shipment-leads.excluded-domains.toggle', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="{{ $item->is_active ? 'Pause Rule' : 'Activate Rule' }}" style="font-size: 0.75rem;">
                                            @if($item->is_active)
                                                <i class="fa-solid fa-pause"></i>
                                            @else
                                                <i class="fa-solid fa-play"></i>
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('shipment-leads.excluded-domains.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $item->domain }} from excluded domains list?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete Domain Rule" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-shield-cat fa-2x mb-2 text-secondary opacity-50"></i>
                                <div>No excluded domains added yet.</div>
                                <div class="small">Add domains like <code>mesk.com</code> above to prevent automated emails from becoming leads.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($excludedDomains->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $excludedDomains->links() }}
            </div>
        @endif
    </div>

    <!-- Informational Note -->
    <div class="mt-3 p-3 rounded-3 bg-light border text-muted" style="font-size: 0.75rem; line-height: 1.5;">
        <i class="fa-solid fa-circle-info text-primary me-1"></i>
        <strong>How Excluded Domains Work:</strong>
        When IMAP synchronization runs on your connected email accounts, the sender's domain (e.g. <code>notice@mesk.com</code> or <code>rates@apac.maersk.com</code>) is checked against this table. If the domain matches any active rule, the email is logged as an excluded domain and will never be inserted into the Leads table.
    </div>
</div>
@endsection
