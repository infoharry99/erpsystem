@extends('shipment_leads.layouts.app')

@section('title', 'Change Password')
@section('page_title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 py-2 px-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-key" style="font-size: 0.85rem;"></i>
                </div>
                <div>
                    <h6 class="m-0 fw-bold text-dark" style="font-size: 0.9rem;">Update Password</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Ensure your account is using a secure password</span>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 form-compact">
                <form method="POST" action="{{ route('shipment-leads.profile.change-password') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top mt-3">
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
                            <i class="fa-solid fa-check me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
