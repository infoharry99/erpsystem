<div class="table-responsive">
    <table class="table table-hover align-middle m-0" style="font-size: 0.8125rem;">
        <thead class="table-light text-muted" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <tr>
                <th style="width: 100px;"># / Date</th>
                <th style="min-width: 250px;">Subject & AI Summary</th>
                <th style="width: 160px;">Route</th>
                <th style="width: 90px;">Type</th>
                <th style="width: 140px;">Mailbox</th>
                <th style="width: 130px;">Status</th>
                <th class="text-center" style="width: 90px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tabLeads as $lead)
                <tr class="{{ $lead->reply_status === 'not_replied' ? 'bg-light-subtle' : '' }}">
                    <!-- # & Date -->
                    <td>
                        <div class="d-flex align-items-center gap-1.5">
                            @if(!$lead->is_read)
                                <span class="d-inline-block rounded-circle bg-primary" style="width: 7px; height: 7px; min-width: 7px;" title="Unread in Mailbox"></span>
                            @endif
                            <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="fw-bold text-decoration-none {{ !$lead->is_read ? 'text-primary' : 'text-dark' }}">
                                #{{ $lead->id }}
                            </a>
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem; white-space: nowrap;">
                            @if($lead->received_date)
                                {{ \Carbon\Carbon::parse($lead->received_date)->timezone('Europe/London')->format('M d, H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </td>

                    <!-- Subject & Summary -->
                    <td>
                        <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="text-decoration-none text-truncate d-block fw-semibold {{ !$lead->is_read ? 'text-dark' : 'text-secondary' }}" style="max-width: 380px;" title="{{ $lead->email_subject }}">
                            {{ $lead->email_subject ?: '(No Subject)' }}
                        </a>
                        @if($lead->ai_summary)
                            <div class="text-secondary text-truncate" style="font-size: 0.72rem; max-width: 380px;" title="{{ $lead->ai_summary }}">
                                <i class="fa-solid fa-wand-magic-sparkles text-primary me-1" style="font-size: 0.65rem;"></i>{{ $lead->ai_summary }}
                            </div>
                        @endif
                    </td>

                    <!-- Route -->
                    <td>
                        @if($lead->origin || $lead->destination)
                            <div class="d-inline-flex align-items-center px-2 py-0.5 rounded bg-light border text-truncate" style="font-size: 0.74rem; max-width: 170px;" title="{{ ($lead->origin ?: 'TBD') . ' → ' . ($lead->destination ?: 'TBD') }}">
                                <span class="fw-semibold text-dark text-truncate">{{ $lead->origin ?: 'TBD' }}</span>
                                <i class="fa-solid fa-arrow-right text-muted mx-1" style="font-size: 0.6rem;"></i>
                                <span class="fw-semibold text-dark text-truncate">{{ $lead->destination ?: 'TBD' }}</span>
                            </div>
                        @else
                            <span class="text-muted" style="font-size: 0.75rem;">-</span>
                        @endif
                    </td>

                    <!-- Type -->
                    <td>
                        <span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">
                            {{ $lead->shipment_type_label }}
                        </span>
                    </td>

                    <!-- Mailbox -->
                    <td>
                        <div class="text-muted text-truncate" style="font-size: 0.72rem; max-width: 140px;" title="{{ $lead->account->email ?? 'N/A' }}">
                            <i class="fa-regular fa-envelope me-1 text-secondary"></i>{{ $lead->account->email ?? 'N/A' }}
                        </div>
                    </td>

                    <!-- Status Badges -->
                    <td>
                        <div class="d-flex flex-column gap-1 align-items-start">
                            <!-- Lead Funnel Stage Badge -->
                            @if($lead->lead_status === 'final_lead')
                                <span class="badge rounded-pill px-2 py-0.5" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-weight: 600; font-size: 0.7rem;" title="Final Lead (QGLT + GLT)">
                                    <i class="fa-solid fa-flag-checkered me-1" style="font-size: 0.6rem;"></i>Final Lead
                                </span>
                            @elseif($lead->lead_status === 'quotation_sent')
                                <span class="badge rounded-pill px-2 py-0.5" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 600; font-size: 0.7rem;" title="Quotation Sent (QGLT)">
                                    <i class="fa-solid fa-file-invoice-dollar me-1" style="font-size: 0.6rem;"></i>Quotation Sent
                                </span>
                            @elseif($lead->lead_status === 'new')
                                <span class="badge rounded-pill px-2 py-0.5" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; font-size: 0.7rem;" title="New Lead">
                                    <i class="fa-solid fa-sparkles me-1" style="font-size: 0.6rem;"></i>New Lead
                                </span>
                            @else
                                <span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $lead->lead_status) }}
                                </span>
                            @endif

                            <!-- Replied Badge -->
                            @if($lead->reply_status === 'replied')
                                @if(!in_array($lead->lead_status, ['quotation_sent', 'final_lead']))
                                    <span class="badge rounded-pill px-2 py-0.5" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 0.68rem; font-weight: 600;">
                                        <i class="fa-solid fa-circle-check me-1"></i>Replied
                                    </span>
                                @endif
                            @else
                                <span class="badge rounded-pill px-2 py-0.5" style="background-color: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; font-size: 0.68rem; font-weight: 600;">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>Not Replied
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- Action -->
                    <td class="text-center">
                        <a href="{{ route('shipment-leads.leads.show', $lead->id) }}" class="btn btn-outline-primary btn-sm px-2.5 py-1" style="border-radius: 6px; font-size: 0.75rem; font-weight: 600;" title="Open Lead Details">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                        <p class="m-0 fw-semibold" style="font-size: 0.85rem;">No leads in this category for this customer.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
