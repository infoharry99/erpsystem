@extends('shipment_leads.layouts.app')

@section('title', 'Team Users Management')
@section('page_title', 'User Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.95rem;">
            Team Users & Sales Reps
        </h6>
        <p class="text-muted m-0" style="font-size: 0.78rem;">
            Manage team members authorized to access and handle shipment leads.
        </p>
    </div>
    <a href="{{ route('shipment-leads.users.create') }}" class="btn btn-primary btn-sm px-3" style="border-radius: 8px; font-size: 0.8125rem; font-weight: 600;">
        <i class="fa-solid fa-user-plus me-1"></i> Add New User
    </a>
</div>

<div class="card card-modern shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
        <span class="fw-bold text-dark" style="font-size: 0.85rem;">
            All System Users
        </span>
        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.7rem; border-radius: 10px;">
            {{ $users->count() }} users
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table-leads m-0">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 28%;">Name & Email</th>
                        <th style="width: 22%;">Assigned Leads</th>
                        <th style="width: 22%;">Member Since</th>
                        <th style="width: 18%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <span class="fw-bold text-muted" style="font-size: 0.75rem;">#{{ $user->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #e0f2fe; color: #0284c7; font-size: 0.75rem; font-weight: 700;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dark" style="font-size: 0.8125rem;">{{ $user->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border" style="font-size: 0.72rem; border-radius: 6px;">
                                    {{ $user->assigned_leads_count }} leads
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('shipment-leads.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm px-2 py-0.5" style="border-radius: 6px; font-size: 0.72rem;" title="Edit User">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    @if(Auth::id() !== $user->id)
                                        <form action="{{ route('shipment-leads.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-0.5" style="border-radius: 6px; font-size: 0.72rem;" title="Delete User">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted" style="font-size: 0.8125rem;">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
