@extends('shipment_leads.layouts.app')

@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('page_title', $user->exists ? 'Edit User' : 'Add New User')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <!-- Back navigation link -->
        <div class="mb-2">
            <a href="{{ route('shipment-leads.users.index') }}" class="text-decoration-none text-muted" style="font-size: 0.8125rem;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to User Management
            </a>
        </div>

        <div class="card card-modern shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-user" style="font-size: 0.9rem;"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.9rem;">
                            {{ $user->exists ? 'Edit User Details' : 'Create Team User' }}
                        </h6>
                        <span class="text-muted" style="font-size: 0.72rem;">
                            {{ $user->exists ? $user->email : 'Add staff member for handling and assigning shipment leads' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 p-md-4 form-compact">
                <form method="POST" action="{{ $user->exists ? route('shipment-leads.users.update', $user->id) : route('shipment-leads.users.store') }}">
                    @csrf
                    @if($user->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="john@company.com" value="{{ old('email', $user->email) }}" required>
                        <span class="form-hint">Used for login and team assignment notifications</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password {{ $user->exists ? '(Leave blank to keep unchanged)' : '*' }}</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" {{ $user->exists ? '' : 'required' }}>
                        <span class="form-hint">{{ $user->exists ? 'Leave empty to maintain existing password' : 'At least 6 characters' }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-3">
                        <a href="{{ route('shipment-leads.users.index') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem;">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
                            <i class="fa-solid fa-check me-1"></i> {{ $user->exists ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
