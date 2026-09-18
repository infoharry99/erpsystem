@extends('shipment_leads.layouts.app')

@section('title', $account->exists ? 'Edit Email Account' : 'Add Email Account')
@section('page_title', $account->exists ? 'Edit Email Account' : 'Add Email Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <!-- Back navigation link -->
        <div class="mb-2">
            <a href="{{ route('shipment-leads.accounts.index') }}" class="text-decoration-none text-muted" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Email Accounts
            </a>
        </div>

        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-at" style="font-size: 0.9rem;"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.9rem;">
                            {{ $account->exists ? 'Edit Email Account' : 'Configure New Email Account' }}
                        </h6>
                        <span class="text-muted" style="font-size: 0.72rem;">
                            {{ $account->exists ? $account->email : 'Connect company mailbox via IMAP for automated lead processing' }}
                        </span>
                    </div>
                </div>
                @if($account->exists)
                    <span class="badge {{ $account->status === 'active' ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem; border-radius: 6px;">
                        {{ ucfirst($account->status) }}
                    </span>
                @endif
            </div>

            <div class="card-body p-3 p-md-4 form-compact">
                <form id="accountForm" method="POST" action="{{ $account->exists ? route('shipment-leads.accounts.update', $account->id) : route('shipment-leads.accounts.store') }}">
                    @csrf
                    @if($account->exists)
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $account->id }}">
                    @endif

                    <!-- Section: General Information -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-id-card me-1"></i> General Information
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Quotes Team / Sales Desk" value="{{ old('name', $account->name) }}" required>
                            <span class="form-hint">Internal friendly name for mailbox</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="quotes@company.com" value="{{ old('email', $account->email) }}" required>
                            <span class="form-hint">The full email address to monitor</span>
                        </div>
                    </div>

                    <!-- Section: IMAP Settings -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-server me-1"></i> IMAP Connection Settings
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <label class="form-label">IMAP Host <span class="text-danger">*</span></label>
                            <input type="text" name="imap_host" class="form-control" placeholder="imap.gmail.com" value="{{ old('imap_host', $account->imap_host) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">IMAP Port <span class="text-danger">*</span></label>
                            <input type="number" name="imap_port" class="form-control" value="{{ old('imap_port', $account->imap_port ?: 993) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Encryption</label>
                            <select name="imap_encryption" class="form-select">
                                <option value="ssl" {{ old('imap_encryption', $account->imap_encryption) === 'ssl' ? 'selected' : '' }}>SSL (Port 993)</option>
                                <option value="tls" {{ old('imap_encryption', $account->imap_encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="none" {{ old('imap_encryption', $account->imap_encryption) === 'none' ? 'selected' : '' }}>None (Port 143)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">IMAP Username <span class="text-danger">*</span></label>
                            <input type="text" name="imap_username" class="form-control" placeholder="admin@company.com" value="{{ old('imap_username', $account->imap_username) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IMAP Password {{ $account->exists ? '(Leave blank to keep current)' : '*' }}</label>
                            <input type="password" name="imap_password" class="form-control" placeholder="{{ $account->exists ? '••••••••••••••••' : 'App Password or email password' }}" {{ $account->exists ? '' : 'required' }}>
                            <span class="form-hint"><i class="fa-solid fa-circle-info me-1"></i>For Gmail, use a 16-character Google App Password</span>
                        </div>
                    </div>

                    <!-- Section: Mailbox Folders & Status -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-folder-tree me-1"></i> Folder & Sync Configuration
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Inbox Folder</label>
                            <input type="text" name="inbox_folder" class="form-control" value="{{ old('inbox_folder', $account->inbox_folder ?: 'INBOX') }}" required>
                            <span class="form-hint">Default is <code>INBOX</code></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sent Folder</label>
                            <input type="text" name="sent_folder" class="form-control" value="{{ old('sent_folder', $account->sent_folder ?: 'Sent') }}" required>
                            <span class="form-hint">For reply tracking</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sync Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Active (Auto Sync)</option>
                                <option value="inactive" {{ old('status', $account->status) === 'inactive' ? 'selected' : '' }}>Inactive (Disabled)</option>
                            </select>
                            <span class="form-hint">Enable or pause syncing</span>
                        </div>
                    </div>

                    <!-- Connection Test Feedback Box -->
                    <div id="testResultBox" class="alert d-none mb-3 py-2 px-3" style="font-size: 0.8125rem; border-radius: 8px;" role="alert"></div>

                    <!-- Form Footer Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top mt-3 gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;" onclick="testImapConnection()">
                            <i class="fa-solid fa-plug me-1" id="testSpinner"></i> Test Connection
                        </button>

                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('shipment-leads.accounts.index') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem;">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
                                <i class="fa-solid fa-check me-1"></i> Save Account
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testImapConnection() {
        const form = document.getElementById('accountForm');
        const formData = new FormData(form);
        formData.delete('_method'); // Avoid method spoofing overriding POST to PUT on edit page

        const spinner = document.getElementById('testSpinner');
        const box = document.getElementById('testResultBox');

        spinner.classList.add('fa-spin');
        box.classList.add('d-none');

        fetch("{{ route('shipment-leads.accounts.test-connection') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok) {
                throw new Error((data && data.message) ? data.message : `HTTP ${res.status}: ${res.statusText}`);
            }
            return data;
        })
        .then(data => {
            spinner.classList.remove('fa-spin');
            box.classList.remove('d-none', 'alert-success', 'alert-danger');

            if (data && data.success) {
                box.classList.add('alert-success');
                box.innerHTML = `<i class="fa-solid fa-circle-check me-2"></i> ${data.message}`;
            } else {
                box.classList.add('alert-danger');
                box.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> ${data ? data.message : 'Connection test failed.'}`;
            }
        })
        .catch(err => {
            spinner.classList.remove('fa-spin');
            box.classList.remove('d-none', 'alert-success');
            box.classList.add('alert-danger');
            box.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> ${err.message || 'Connection test failed. Check server parameters.'}`;
        });
    }
</script>
@endpush
