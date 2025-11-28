@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-clipboard-list me-2 text-warning"></i>
            My Requests
            <small class="text-muted fw-normal ms-2">
                <i class="fas fa-clock me-1"></i>Most recent first
            </small>
        </h2>
        <a href="{{ route('faculty.requests.create') }}"
           class="btn btn-warning fw-bold">
            <i class="fas fa-plus me-1"></i>
            New Request
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">Pending</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->where('status', 'pending')->count() }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">Admin Approved</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->where('status', 'approved_by_admin')->count() }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">Ready for Pickup</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->where('status', 'fulfilled')->count() }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-box-open fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-secondary text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">Completed</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->where('status', 'claimed')->count() + $requests->where('status', 'cancelled')->count() }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-handshake fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('faculty.requests.index') }}" class="row g-3" id="filterForm">
                    <div class="col-md-6">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by item name or purpose..."
                               class="form-control"
                               id="searchInput">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select" id="statusSelect">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved_by_admin" {{ request('status') === 'approved_by_admin' ? 'selected' : '' }}>Admin Approved</option>
                            <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Ready for Pickup</option>
                            <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Completed</option>
                            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'status']))
                                <a href="{{ route('faculty.requests.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests List -->
        <div class="row g-4">
            @forelse($requests as $request)
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Item Info -->
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="fas fa-box text-primary fa-lg"></i>
                                        </div>
                                        <div>
                                            @if($request->requestItems->count() > 0)
                                                <h5 class="mb-1">{{ $request->requestItems->first()->itemable ? $request->requestItems->first()->itemable->name : 'Multiple Items' }}</h5>
                                                @if($request->requestItems->count() > 1)
                                                    <p class="text-muted mb-1">+{{ $request->requestItems->count() - 1 }} more item{{ $request->requestItems->count() > 2 ? 's' : '' }}</p>
                                                @else
                                                    <p class="text-muted mb-1">Qty: {{ $request->requestItems->first()->quantity }} {{ $request->requestItems->first()->itemable && $request->requestItems->first()->itemable->unit ? $request->requestItems->first()->itemable->unit : 'pcs' }}</p>
                                                @endif
                                            @else
                                                <h5 class="mb-1">Item Not Found</h5>
                                                <p class="text-muted mb-1">Qty: 0 pcs</p>
                                            @endif
                                            <small class="text-muted">{{ $request->department }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Priority -->
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <span class="badge fs-6 px-3 py-2
                                            @switch($request->status)
                                                @case('pending') bg-primary @break
                                                @case('approved_by_admin') bg-success @break
                                                @case('ready_for_pickup') bg-purple text-white @break
                                                @case('fulfilled') bg-purple text-white @break
                                                @case('claimed') bg-secondary @break
                                                @case('declined') bg-danger @break
                                                @case('cancelled') bg-secondary @break
                                                @default bg-secondary @break
                                            @endswitch">
                                            {{ $request->getStatusDisplayName() }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge
                                            @switch($request->priority)
                                                @case('low') bg-success @break
                                                @case('normal') bg-primary @break
                                                @case('high') bg-warning @break
                                                @case('urgent') bg-danger @break
                                            @endswitch">
                                            {{ ucfirst($request->priority) }} Priority
                                        </span>
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="col-md-3">
                                    <div class="small">
                                        <div><strong>Requested:</strong> {{ $request->created_at ? $request->created_at->format('M j, Y') : 'N/A' }}</div>
                                        <div><strong>Needed:</strong> {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</div>
                                        @if($request->fulfilled_date)
                                            <div class="text-success"><strong>Ready:</strong> {{ $request->fulfilled_date->format('M j, Y') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-md-2 text-end">
                                    <a href="{{ route('faculty.requests.show', $request) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>

                            @if($request->purpose)
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted">
                                            <strong>Purpose:</strong> {{ Str::limit($request->purpose, 100) }}
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No requests found</h5>
                            <p class="text-muted mb-3">You haven't submitted any requests yet.</p>
                            <a href="{{ route('faculty.requests.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus me-1"></i>Submit Your First Request
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Requests pagination" class="d-flex justify-content-center">
                        {{ $requests->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.getElementById('statusSelect');

    let searchTimeout;

    // Auto-submit on status changes
    statusSelect.addEventListener('change', function() {
        filterForm.submit();
    });

    // Debounced search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 2 || this.value.length === 0) {
                filterForm.submit();
            }
        }, 500);
    });

    // Prevent form submission with only empty values
    filterForm.addEventListener('submit', function(e) {
        const searchVal = searchInput.value.trim();
        const statusVal = statusSelect.value;

        // If all fields are empty, redirect to clean URL
        if (!searchVal && !statusVal) {
            e.preventDefault();
            window.location.href = '{{ route("faculty.requests.index") }}';
            return false;
        }
    });
});
</script>
@endpush

<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
</style>