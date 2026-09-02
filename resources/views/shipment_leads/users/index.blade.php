@extends('shipment_leads.layouts.app')

@section('title', 'Team Users Management')
@section('page_title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted m-0">Manage system users and sales representatives responsible for handling leads.</p>
    <a href="{{ route('shipment-leads.users.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-user-plus me-1"></i> Add New User
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Assigned Leads</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td><span class="text-primary">{{ $user->email }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $user->assigned_leads_count }} leads</span></td>
                            <td><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('shipment-leads.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Edit User">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    @if(Auth::id() !== $user->id)
                                        <form action="{{ route('shipment-leads.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete User">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
