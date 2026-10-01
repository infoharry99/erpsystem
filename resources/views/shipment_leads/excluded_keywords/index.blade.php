@extends('shipment_leads.layouts.app')

@section('title', 'Excluded Subject Keywords - Shipment CRM')
@section('page_title', 'Excluded Keywords & Phrases')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold text-dark m-0" style="font-size: 1.1rem;">
                <i class="fa-solid fa-filter-circle-xmark text-danger me-2"></i>Excluded Subject Keywords & Phrases
            </h5>
            <p class="text-muted m-0" style="font-size: 0.78rem;">
                Incoming emails whose subject contains any of these words or phrases (e.g. <code>payment due</code>, <code>statement</code>, <code>monthly report</code>) will be <strong>skipped and not counted as leads</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shipment-leads.excluded-domains.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-globe me-1"></i> Manage Excluded Domains
            </a>
            <form action="{{ route('shipment-leads.excluded-keywords.prune') }}" method="POST" onsubmit="return confirm('Scan and remove any existing leads whose subject matches excluded keywords?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Scan and delete existing leads matching excluded keywords">
                    <i class="fa-solid fa-broom me-1"></i> Clean / Prune Matching Leads
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs between Domains and Keywords -->
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('shipment-leads.excluded-domains.index') }}" class="btn btn-sm btn-light border text-muted px-3">
            <i class="fa-solid fa-ban me-1"></i> Excluded Domains
        </a>
        <a href="{{ route('shipment-leads.excluded-keywords.index') }}" class="btn btn-sm btn-primary px-3 shadow-none">
            <i class="fa-solid fa-font me-1"></i> Excluded Subject Keywords / Phrases
        </a>
    </div>

    <!-- Add Excluded Keyword Card -->
    <div class="card card-modern shadow-sm p-3 mb-4" style="background: #ffffff; border-left: 4px solid var(--gt-primary);">
        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
            <i class="fa-solid fa-plus-circle text-primary me-1"></i> Add Subject Keyword or Phrase
        </h6>
        <form action="{{ route('shipment-leads.excluded-keywords.store') }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted fw-semibold small mb-1">Keyword or Phrase <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-font text-secondary"></i></span>
                        <input type="text" name="keyword" class="form-control @error('keyword') is-invalid @enderror" placeholder="e.g. payment reminder or monthly report" value="{{ old('keyword') }}" required>
                    </div>
                    @error('keyword')
                        <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted fw-semibold small mb-1">Description / Reason (Optional)</label>
                    <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Accounting notifications, internal status updates" value="{{ old('description') }}">
                </div>
                <div class="col-md-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="prune_existing" id="pruneExistingKeyword" value="1" checked>
                        <label class="form-check-label text-muted small" for="pruneExistingKeyword" style="font-size: 0.75rem;">
                            Auto-delete existing leads with this phrase
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="fa-solid fa-filter-circle-xmark me-1"></i> Add Keyword Rule
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Excluded Keywords Listing -->
    <div class="card card-modern shadow-sm">
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold text-dark m-0" style="font-size: 0.9rem;">
                    Active Subject Keywords & Phrases ({{ $excludedKeywords->total() }})
                </h6>
            </div>
            <div>
                <form action="{{ route('shipment-leads.excluded-keywords.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 260px;">
                        <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('shipment-leads.excluded-keywords.index') }}" class="btn btn-outline-secondary" title="Clear Search">
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
                        <th class="ps-3" style="width: 30%;">Keyword / Phrase</th>
                        <th style="width: 30%;">Description / Purpose</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;">Added On</th>
                        <th class="text-end pe-3" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($excludedKeywords as $item)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fee2e2; color: #ef4444; font-size: 0.75rem;">
                                        <i class="fa-solid fa-font"></i>
                                    </span>
                                    <span>
                                        <code class="text-danger fw-bold" style="font-size: 0.85rem;">"{{ $item->keyword }}"</code>
                                        <div class="text-muted fw-normal" style="font-size: 0.7rem;">
                                            Matches any subject containing this phrase
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
                                    <form action="{{ route('shipment-leads.excluded-keywords.toggle', $item->id) }}" method="POST" class="d-inline">
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

                                    <form action="{{ route('shipment-leads.excluded-keywords.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove \"{{ $item->keyword }}\" from excluded keywords list?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete Keyword Rule" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-font fa-2x mb-2 text-secondary opacity-50"></i>
                                <div>No excluded keywords or phrases added yet.</div>
                                <div class="small">Add phrases like <code>payment due</code> or <code>monthly report</code> above to prevent them from becoming leads.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($excludedKeywords->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $excludedKeywords->links() }}
            </div>
        @endif
    </div>

    <!-- Informational Note -->
    <div class="mt-3 p-3 rounded-3 bg-light border text-muted" style="font-size: 0.75rem; line-height: 1.5;">
        <i class="fa-solid fa-circle-info text-primary me-1"></i>
        <strong>How Excluded Keywords Work:</strong>
        Whenever an incoming email is synced from your connected email accounts, the email subject is tested against active keywords and phrases. If the subject contains any of these phrases, the email is logged as an excluded keyword and is immediately skipped without creating a lead.
    </div>
</div>
@endsection
