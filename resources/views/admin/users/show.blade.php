@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <div>
                        <h1 class="h2 mb-1 text-dark fw-bold">{{ $user->name }}</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user me-1"></i>
                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }} Account
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        @can('admin')
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                Edit User
                            </a>
                        @endcan
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Users
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- User Information -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-circle me-2"></i>
                                    User Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Full Name</p>
                                        <p class="h6 mb-0">{{ $user->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Username</p>
                                        <p class="h6 mb-0">{{ $user->username }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Email Address</p>
                                        <p class="h6 mb-0">{{ $user->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Type</p>
                                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }} fs-6">
                                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }}
                                        </span>
                                    </div>
                                    @if($user->office)
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Office</p>
                                        <p class="h6 mb-0">{{ $user->office->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Account Statistics -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Account Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 text-center">
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-primary mb-1">{{ $user->requests->count() }}</div>
                                        <p class="text-muted small mb-0">Total Requests</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-success mb-1">{{ $user->requests->where('status', 'completed')->count() }}</div>
                                        <p class="text-muted small mb-0">Completed Requests</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-warning mb-1">{{ $user->requests->where('status', 'pending')->count() }}</div>
                                        <p class="text-muted small mb-0">Pending Requests</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        @if($requests->count() > 0)
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Request History
                                </h5>
                                <span class="badge bg-info">{{ $requests->total() }} total requests</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($requests as $request)
                                            <tr>
                                                <td>{{ $request->item->name ?? 'N/A' }}</td>
                                                <td>{{ $request->quantity }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $request->status === 'completed' ? 'success' : ($request->status === 'pending' ? 'warning' : ($request->status === 'claimed' ? 'primary' : 'secondary')) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $request->created_at->format('M j, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if($requests->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $requests->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Account Details -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Account Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Joined:</span>
                                    <span class="fw-medium">{{ $user->created_at->format('M j, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Last Updated:</span>
                                    <span class="fw-medium">{{ $user->updated_at->format('M j, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        @can('admin')
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit User
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete User
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="btn btn-secondary" title="You cannot delete your own account">
                                            <i class="fas fa-trash me-1"></i>
                                            Delete User
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection