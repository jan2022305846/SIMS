@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-clipboard-list me-2 text-warning"></i>
            My Requests
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
        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">Pending</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->where('workflow_status', 'pending')->count() }}</h3>
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
                                <h6 class="card-title mb-1">Approved</h6>
                                <h3 class="mb-0 fw-bold">{{ $requests->whereIn('workflow_status', ['approved_by_office_head', 'approved_by_admin'])->count() }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $requests->where('workflow_status', 'fulfilled')->count() }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $requests->where('workflow_status', 'claimed')->count() }}</h3>
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
                            <option value="approved_by_office_head" {{ request('status') === 'approved_by_office_head' ? 'selected' : '' }}>Office Head Approved</option>
                            <option value="approved_by_admin" {{ request('status') === 'approved_by_admin' ? 'selected' : '' }}>Admin Approved</option>
                            <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Ready for Pickup</option>
                            <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Completed</option>
                            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
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
                                            <h5 class="mb-1">{{ $request->item->name }}</h5>
                                            <p class="text-muted mb-1">Qty: {{ $request->quantity }} {{ $request->item->unit ?? 'pcs' }}</p>
                                            <small class="text-muted">{{ $request->department }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Priority -->
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <span class="badge fs-6 px-3 py-2
                                            @switch($request->workflow_status)
                                                @case('pending') bg-warning @break
                                                @case('approved_by_office_head') bg-info @break
                                                @case('approved_by_admin') bg-success @break
                                                @case('fulfilled') bg-purple text-white @break
                                                @case('claimed') bg-secondary @break
                                                @default bg-danger @break
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
                                        <div><strong>Requested:</strong> {{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</div>
                                        <div><strong>Needed:</strong> {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</div>
                                        @if($request->fulfilled_date)
                                            <div class="text-success"><strong>Ready:</strong> {{ $request->fulfilled_date->format('M j, Y') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-md-2 text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('faculty.requests.show', $request) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($request->isFulfilled() || $request->isClaimed())
                                            <a href="{{ route('requests.claim-slip', $request) }}"
                                               class="btn btn-outline-warning btn-sm"
                                               target="_blank"
                                               title="Print Claim Slip">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif

                                        @if($request->canBeAcknowledgedByRequester())
                                            <a href="{{ route('faculty.requests.acknowledgment.show', $request) }}"
                                               class="btn btn-outline-success btn-sm"
                                               title="Acknowledge Receipt">
                                                <i class="fas fa-signature"></i>
                                            </a>
                                        @endif
                                    </div>
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
                        {{ $requests->appends(request()->query())->links('pagination::bootstrap-4') }}
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