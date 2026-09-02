@extends('shipment_leads.layouts.app')

@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('page_title', $user->exists ? 'Edit User' : 'Add New User')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-dark">
                    <i class="fa-solid fa-user text-primary me-2"></i>
                    {{ $user->exists ? 'Edit User Details' : 'Create Team User' }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ $user->exists ? route('shipment-leads.users.update', $user->id) : route('shipment-leads.users.store') }}">
                    @csrf
                    @if($user->exists)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="john@company.com" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Password {{ $user->exists ? '(Leave blank to keep unchanged)' : '*' }}</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" {{ $user->exists ? '' : 'required' }}>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('shipment-leads.users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $user->exists ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
