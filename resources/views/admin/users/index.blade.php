@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-users me-2 text-warning"></i>
                        User Management
                    </h2>
                    <a href="{{ route('users.create') }}" 
                       class="btn btn-warning fw-bold">
                        <i class="fas fa-plus me-1"></i>
                        Add New User
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                <!-- Search and Filters -->
                <div class="mb-4">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search users..." 
                                   class="form-control">
                        </div>
                        <div class="col-md-3">
                            <select name="office_id" class="form-select">
                                <option value="">All Offices</option>
                                @foreach(\App\Models\Office::orderBy('name')->get() as $office)
                                    <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                Search
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'office_id']))
                            <div class="col-md-1">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-1"></i>
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>
                    @if(request()->hasAny(['search', 'office_id']))
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        @if(request('search'))
                                            Search: "{{ request('search') }}"
                                        @endif
                                        @if(request('office_id'))
                                            @if(request('search')) | @endif
                                            Office: {{ \App\Models\Office::find(request('office_id'))?->name ?? 'Unknown' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Type</th>
                                <th scope="col">Office</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->username }}</div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->office?->name ?: 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('users.show', $user) }}" 
                                               class="btn btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" 
                                               class="btn btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <div>No users found.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }} users
                        </div>
                        <nav aria-label="Users pagination">
                            {{ $users->links('pagination::simple-bootstrap-5') }}
                        </nav>
                    </div>
                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <small class="text-white-50">just now</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        var toast = new bootstrap.Toast(document.getElementById('successToast'));
        toast.show();
    });
@endif
</script>
@endpush
@endsection

@push('styles')
<style>
/* Dark mode fixes for pagination */
[data-theme="dark"] .pagination .page-link {
    background: var(--bg-primary) !important;
    border-color: var(--border-color) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .pagination .page-link:hover {
    background: var(--bg-tertiary) !important;
    border-color: var(--accent-primary) !important;
    color: var(--accent-primary) !important;
}

[data-theme="dark"] .pagination .page-item.active .page-link {
    background: var(--accent-primary) !important;
    border-color: var(--accent-primary) !important;
    color: #ffffff !important;
}

[data-theme="dark"] .pagination .page-item.disabled .page-link {
    background: var(--bg-secondary) !important;
    border-color: var(--border-color) !important;
    color: var(--text-muted) !important;
}

/* Ensure pagination is properly centered and aligned */
nav[aria-label="Users pagination"] .pagination {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 0.25rem !important;
    margin: 0 !important;
}

nav[aria-label="Users pagination"] .pagination .page-item {
    margin: 0 !important;
}

nav[aria-label="Users pagination"] .pagination .page-link {
    border-radius: 0.375rem !important;
    margin: 0 1px !important;
    border: 1px solid #dee2e6 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 40px !important;
    height: 38px !important;
    text-decoration: none !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
}

/* Mobile responsive fixes */
@media (max-width: 576px) {
    nav[aria-label="Users pagination"] .pagination {
        gap: 0.125rem !important;
        justify-content: center !important;
    }

    nav[aria-label="Users pagination"] .pagination .page-link {
        padding: 0.375rem 0.5rem !important;
        font-size: 0.875rem !important;
        min-width: 35px !important;
        height: 35px !important;
    }
}

/* Fix clear button alignment - ensure icon and text are horizontally aligned */
.btn-outline-secondary i.fas.fa-times,
.btn-outline-secondary:has(i.fas.fa-times) {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.25rem !important;
}

.btn-outline-secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.25rem !important;
}
</style>
@endpush
