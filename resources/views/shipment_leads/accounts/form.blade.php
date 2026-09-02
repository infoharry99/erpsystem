@extends('shipment_leads.layouts.app')

@section('title', $account->exists ? 'Edit Email Account' : 'Add Email Account')
@section('page_title', $account->exists ? 'Edit Email Account' : 'Add Email Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-dark">
                    <i class="fa-solid fa-at text-primary me-2"></i>
                    {{ $account->exists ? 'Edit Email Account: ' . $account->email : 'Configure New Email Account' }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form id="accountForm" method="POST" action="{{ $account->exists ? route('shipment-leads.accounts.update', $account->id) : route('shipment-leads.accounts.store') }}">
                    @csrf
                    @if($account->exists)
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $account->id }}">
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sales Desk / Quotes Team" value="{{ old('name', $account->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. quotes@company.com" value="{{ old('email', $account->email) }}" required>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="fa-solid fa-server me-2"></i> IMAP Settings (Incoming Email)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">IMAP Host <span class="text-danger">*</span></label>
                            <input type="text" name="imap_host" class="form-control" placeholder="imap.gmail.com / imap.company.com" value="{{ old('imap_host', $account->imap_host) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">IMAP Port <span class="text-danger">*</span></label>
                            <input type="number" name="imap_port" class="form-control" value="{{ old('imap_port', $account->imap_port ?: 993) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Encryption</label>
                            <select name="imap_encryption" class="form-select">
                                <option value="ssl" {{ old('imap_encryption', $account->imap_encryption) === 'ssl' ? 'selected' : '' }}>SSL (Recommended)</option>
                                <option value="tls" {{ old('imap_encryption', $account->imap_encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="none" {{ old('imap_encryption', $account->imap_encryption) === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">IMAP Username <span class="text-danger">*</span></label>
                            <input type="text" name="imap_username" class="form-control" value="{{ old('imap_username', $account->imap_username) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IMAP Password {{ $account->exists ? '(Leave blank to keep unchanged)' : '*' }}</label>
                            <input type="password" name="imap_password" class="form-control" {{ $account->exists ? '' : 'required' }}>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="fa-solid fa-folder-tree me-2"></i> Folder Configuration</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Inbox Folder Name</label>
                            <input type="text" name="inbox_folder" class="form-control" value="{{ old('inbox_folder', $account->inbox_folder ?: 'INBOX') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sent Folder Name (For Reply Detection)</label>
                            <input type="text" name="sent_folder" class="form-control" value="{{ old('sent_folder', $account->sent_folder ?: 'Sent') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Active (Enabled for Auto Sync)</option>
                            <option value="inactive" {{ old('status', $account->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div id="testResultBox" class="alert d-none mb-3" role="alert"></div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-info" onclick="testImapConnection()">
                            <i class="fa-solid fa-plug me-1" id="testSpinner"></i> Test Connection
                        </button>

                        <div>
                            <a href="{{ route('shipment-leads.accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Account
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
        .then(res => res.json())
        .then(data => {
            spinner.classList.remove('fa-spin');
            box.classList.remove('d-none', 'alert-success', 'alert-danger');

            if (data.success) {
                box.classList.add('alert-success');
                box.innerHTML = `<i class="fa-solid fa-circle-check me-2"></i> ${data.message}`;
            } else {
                box.classList.add('alert-danger');
                box.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> ${data.message}`;
            }
        })
        .catch(err => {
            spinner.classList.remove('fa-spin');
            box.classList.remove('d-none', 'alert-success');
            box.classList.add('alert-danger');
            box.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> Connection test failed. Check server parameters.`;
        });
    }
</script>
@endpush
