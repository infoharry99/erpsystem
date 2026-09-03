@extends('shipment_leads.layouts.app')

@section('title', 'Lead Details #' . $lead->id)
@section('page_title', 'Shipment Lead #' . $lead->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('shipment-leads.leads.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Leads
    </a>

    <div class="d-flex align-items-center gap-2">
        <span class="fs-6">Reply Status:</span>
        @if($lead->reply_status === 'replied')
            <span class="badge badge-replied fs-6 px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i> Replied</span>
        @else
            <span class="badge badge-not-replied fs-6 px-3 py-2"><i class="fa-solid fa-circle-exclamation me-1"></i> Not Replied</span>
        @endif
    </div>
</div>

@if($lead->ai_summary)
<div class="card border-0 shadow-sm mb-4 bg-white border-start border-4 border-primary">
    <div class="card-body p-3">
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-primary me-2"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Email Summary</span>
            <small class="text-muted">Inquiry Overview</small>
        </div>
        <div class="m-0 text-dark fw-semibold fs-6" style="line-height: 1.8;">{!! nl2br(e(html_entity_decode(strip_tags($lead->ai_summary), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !!}</div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white font-weight-bold py-3">
                <i class="fa-solid fa-user-tie text-primary me-2"></i> Customer Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> {{ $lead->customer_name }}</p>
                <p class="mb-1"><strong>Email:</strong> <a href="mailto:{{ $lead->customer_email }}">{{ $lead->customer_email }}</a></p>
                <p class="mb-1"><strong>Phone:</strong> {{ $lead->customer_phone ?: 'Not provided' }}</p>
                <p class="mb-0"><strong>Company:</strong> {{ $lead->company_name ?: 'Not detected' }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white font-weight-bold py-3">
                <i class="fa-solid fa-sliders text-warning me-2"></i> Status & Assignment
            </div>
            <div class="card-body">
                <form action="{{ route('shipment-leads.leads.update-status', $lead->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <label class="form-label font-weight-bold">Lead Status:</label>
                    <div class="input-group">
                        <select name="lead_status" class="form-select">
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ $lead->lead_status === $st ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($st)) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-dark">Update</button>
                    </div>
                </form>

                <form action="{{ route('shipment-leads.leads.assign', $lead->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label class="form-label font-weight-bold">Assigned User:</label>
                    <div class="input-group">
                        <select name="assigned_to" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $lead->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white font-weight-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-truck-ramp-box text-success me-2"></i> Extracted Shipment Specs</span>
                <span class="badge bg-light text-dark border">Incoterm: {{ $lead->incoterms ?: 'N/A' }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('shipment-leads.leads.update-extracted', $lead->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Origin</label>
                            <input type="text" name="origin" class="form-control form-control-sm" value="{{ $lead->origin }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Destination</label>
                            <input type="text" name="destination" class="form-control form-control-sm" value="{{ $lead->destination }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">POL</label>
                            <input type="text" name="pol" class="form-control form-control-sm" value="{{ $lead->pol }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">POD</label>
                            <input type="text" name="pod" class="form-control form-control-sm" value="{{ $lead->pod }}">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small">Pickup Address</label>
                        <textarea name="pickup_address" class="form-control form-control-sm" rows="2">{{ $lead->pickup_address }}</textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small">Delivery Address</label>
                        <textarea name="delivery_address" class="form-control form-control-sm" rows="2">{{ $lead->delivery_address }}</textarea>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Commodity</label>
                            <input type="text" name="commodity" class="form-control form-control-sm" value="{{ $lead->commodity }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Weight</label>
                            <input type="text" name="weight" class="form-control form-control-sm" value="{{ $lead->weight }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Container / Equipment</label>
                            <input type="text" name="container_type" class="form-control form-control-sm" value="{{ $lead->container_type }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Pallets / Quantity</label>
                            <input type="text" name="pallets" class="form-control form-control-sm" value="{{ $lead->pallets }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm btn-success w-100 mt-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Shipment Details
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white font-weight-bold py-3">
                <i class="fa-solid fa-note-sticky text-info me-2"></i> Internal Team Notes
            </div>
            <div class="card-body">
                <form action="{{ route('shipment-leads.leads.add-note', $lead->id) }}" method="POST" class="mb-3">
                    @csrf
                    <textarea name="note" class="form-control mb-2" rows="2" placeholder="Write internal note..." required></textarea>
                    <button type="submit" class="btn btn-sm btn-info text-white">Add Note</button>
                </form>

                <div class="notes-history" style="max-height: 250px; overflow-y: auto;">
                    @forelse($lead->leadNotes as $note)
                        <div class="p-2 mb-2 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-bold">{{ $note->user->name ?? 'Team Member' }}</small>
                                <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-dark d-block">{{ $note->note }}</small>
                        </div>
                    @empty
                        <small class="text-muted">No internal notes added yet.</small>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <ul class="nav nav-tabs card-header-tabs" id="leadTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#emailTabContent" type="button">
                            <i class="fa-solid fa-envelope me-1"></i> Original Email
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="thread-tab" data-bs-toggle="tab" data-bs-target="#threadTabContent" type="button">
                            <i class="fa-solid fa-comments me-1"></i> Conversation Thread ({{ $conversation->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="attachments-tab" data-bs-toggle="tab" data-bs-target="#attachmentsTabContent" type="button">
                            <i class="fa-solid fa-paperclip me-1"></i> Attachments ({{ $lead->email->attachments->count() ?? 0 }})
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="leadTabsContent">
                    <div class="tab-pane fade show active" id="emailTabContent" role="tabpanel">
                        <div class="mb-3 p-3 bg-light rounded border">
                            <h5 class="m-0 font-weight-bold text-dark">{{ $lead->email_subject }}</h5>
                            <hr class="my-2">
                            <div class="row text-muted small">
                                <div class="col-md-6"><strong>From:</strong> {{ $lead->customer_name }} &lt;{{ $lead->customer_email }}&gt;</div>
                                <div class="col-md-6"><strong>Date:</strong> {{ $lead->received_date ? $lead->received_date->format('Y-m-d H:i:s') : 'N/A' }}</div>
                                <div class="col-md-6"><strong>Source Mailbox:</strong> {{ $lead->account->email ?? 'N/A' }}</div>
                                <div class="col-md-6"><strong>Message ID:</strong> {{ $lead->email->message_id ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="email-body-container p-3 border rounded bg-white" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                            {!! $lead->original_content !!}
                        </div>
                    </div>

                    <div class="tab-pane fade" id="threadTabContent" role="tabpanel">
                        <div class="timeline p-2">
                            @forelse($conversation as $msg)
                                <div class="card mb-3 border-0 shadow-sm {{ $msg->direction === 'outgoing' ? 'bg-light border-start border-4 border-success' : 'border-start border-4 border-primary' }}">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <span class="badge {{ $msg->direction === 'outgoing' ? 'bg-success' : 'bg-primary' }} me-2">
                                                {{ ucfirst($msg->direction) }}
                                            </span>
                                            <strong>{{ $msg->from_name }}</strong> &lt;{{ $msg->from_email }}&gt;
                                        </div>
                                        <small class="text-muted">{{ $msg->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                    <div class="card-body py-2">
                                        <h6 class="mb-1 text-muted">Subject: {{ $msg->subject }}</h6>
                                        <div class="p-2 border rounded bg-white mt-2" style="max-height: 250px; overflow-y: auto;">
                                            {!! $msg->body_html ?: nl2br(e($msg->body_text)) !!}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">No full thread history available yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="tab-pane fade" id="attachmentsTabContent" role="tabpanel">
                        @if($lead->email && $lead->email->attachments->count() > 0)
                            <div class="list-group">
                                @foreach($lead->email->attachments as $att)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fa-solid fa-file text-primary me-2"></i>
                                            <strong>{{ $att->filename }}</strong>
                                            <span class="text-muted small">({{ number_format($att->file_size / 1024, 1) }} KB)</span>
                                        </div>
                                        <span class="badge bg-secondary">{{ $att->mime_type }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-paperclip fa-2x mb-2 d-block"></i> No attachments with this email.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
