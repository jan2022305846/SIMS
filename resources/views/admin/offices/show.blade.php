@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-building me-2"></i>{{ $office->name }}
                    </h4>
                    <div>
                        <a href="{{ route('admin.offices.edit', $office) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('admin.offices.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Offices
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width: 150px;">Office Name:</th>
                                    <td>{{ $office->name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Office Code:</th>
                                    <td>
                                        <span class="badge bg-primary">{{ $office->code }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Office Head:</th>
                                    <td>
                                        @if($office->officeHead)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ strtoupper(substr($office->officeHead->name, 0, 1)) }}
                                                </div>
                                                {{ $office->officeHead->name }}
                                                <small class="text-muted ms-1">({{ $office->officeHead->email }})</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Location:</th>
                                    <td>{{ $office->location ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Created:</th>
                                    <td>{{ $office->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Updated:</th>
                                    <td>{{ $office->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Description:</h6>
                                <p class="mb-0">{{ $office->description ?: 'No description provided.' }}</p>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Statistics:</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <div class="stat-number">{{ $office->users->count() }}</div>
                                            <div class="stat-label">Users</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <div class="stat-number">{{ $office->items->count() }}</div>
                                            <div class="stat-label">Items</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($office->users->count() > 0)
                    <div class="mt-4">
                        <h5 class="mb-3">Users in this Office</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($office->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'office_head' ? 'warning' : 'info') }}">
                                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($office->items->count() > 0)
                    <div class="mt-4">
                        <h5 class="mb-3">Items in this Office</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Current Holder</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($office->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($item->isAssigned())
                                                <span class="badge bg-warning">Assigned</span>
                                            @else
                                                <span class="badge bg-success">Available</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->currentHolder)
                                                {{ $item->currentHolder->name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    padding: 1rem 1.25rem;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.stat-card {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    border: 1px solid #dee2e6;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    font-weight: 500;
}

.table th {
    font-weight: 600;
    color: #495057;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.text-muted {
    color: #6c757d !important;
}
</style>
@endpush