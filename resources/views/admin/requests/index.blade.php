@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-clipboard-list me-2 text-warning"></i>
                        Request Management
                    </h2>
                    <div>
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            {{ $requests->total() }} Total Requests
                        </span>
                    </div>
                </div>

                <!-- Quick Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">Pending Review</h6>
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
                                        <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                                        <h3 class="mb-0 fw-bold">{{ $requests->where('status', 'claimed')->count() }}</h3>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Filter and Search Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-filter me-2"></i>Filter & Search
                            </h5>
                            <form method="GET" action="{{ route('requests.manage') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="search" class="form-label fw-semibold">Search</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="search" 
                                               name="search" 
                                               value="{{ request('search') }}"
                                               placeholder="Search by user, item, or purpose...">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status" class="form-label fw-semibold">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                            <option value="approved_by_admin" {{ request('status') === 'approved_by_admin' ? 'selected' : '' }}>Admin Approved</option>
                                            <option value="fulfilled" {{ request('status') === 'fulfilled' ? 'selected' : '' }}>Ready for Pickup</option>
                                            <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Completed</option>
                                            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="priority" class="form-label fw-semibold">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="">All Priorities</option>
                                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="office" class="form-label fw-semibold">Office</label>
                                        <select class="form-select" id="office" name="office">
                                            <option value="">All Offices</option>
                                            @foreach($offices as $office)
                                                <option value="{{ $office->id }}" {{ request('office') == $office->id ? 'selected' : '' }}>
                                                    {{ $office->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="btn-group w-100">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i>Filter
                                            </button>
                                            <a href="{{ route('requests.manage') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Requests Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Request Details</th>
                                        <th scope="col">Item & Quantity</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Priority</th>
                                        <th scope="col">Dates</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $request->user->name }}</div>
                                                        <div class="text-muted small">{{ $request->user->email }}</div>
                                                        @if($request->purpose)
                                                            <div class="text-muted small" title="{{ $request->purpose }}">
                                                                {{ Str::limit($request->purpose, 40) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $request->item ? $request->item->name : 'Item Not Found' }}</div>
                                                    <div class="mb-1">
                                                        <span class="badge bg-info">Qty: {{ $request->quantity }}</span>
                                                        @if($request->item && $request->item->unit)
                                                            <span class="text-muted small">{{ $request->item->unit }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted small">
                                                        Stock: {{ $request->item ? $request->item->current_stock : 'N/A' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @switch($request->status)
                                                        @case('pending') bg-warning @break
                                                        @case('approved_by_admin') bg-success @break
                                                        @case('fulfilled') bg-purple text-white @break
                                                        @case('claimed') bg-secondary @break
                                                        @default bg-danger @break
                                                    @endswitch
                                                    px-2 py-1">
                                                    {{ $request->getStatusDisplayName() }}
                                                </span>
                                                @if($request->status === 'declined_by_admin' && $request->admin_notes)
                                                    <div class="text-muted small mt-1" title="{{ $request->admin_notes }}">
                                                        <i class="fas fa-info-circle me-1"></i>{{ Str::limit($request->admin_notes, 30) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @switch($request->priority)
                                                        @case('low') bg-success @break
                                                        @case('normal') bg-primary @break
                                                        @case('high') bg-warning @break
                                                        @case('urgent') bg-danger @break
                                                    @endswitch">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>Requested:</strong> {{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</div>
                                                    <div><strong>Needed:</strong> {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</div>
                                                    @if($request->fulfilled_date)
                                                        <div class="text-success"><strong>Ready:</strong> {{ $request->fulfilled_date->format('M j, Y') }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('requests.show', $request) }}" 
                                                       class="btn btn-outline-info btn-sm"
                                                       data-bs-toggle="tooltip"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(auth()->user()->isAdmin() && $request->isPending())
                                                        <form method="POST" action="{{ route('requests.destroy', $request) }}" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this request? This action cannot be undone.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-outline-danger btn-sm"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Delete Request">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="fas fa-clipboard-list fa-3x text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted mb-1">No Requests Found</h5>
                                                    <p class="text-muted mb-0">Try adjusting your filter criteria.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($requests->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Showing {{ $requests->firstItem() }}-{{ $requests->lastItem() }} of {{ $requests->total() }} requests
                                </div>
                                <nav aria-label="Requests pagination">
                                    {{ $requests->withQueryString()->links() }}
                                </nav>
                            </div>
                        @else
                            @if($requests->total() > 0)
                                <div class="text-center mt-4 text-muted">
                                    Showing all {{ $requests->total() }} requests
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
</style>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 5000);
        });
    </script>
@endif

@if(session('error') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') ?? $errors->first() }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 5000);
        });
    </script>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-submit form on select change - with protection against conflicts
    const statusFilter = document.getElementById('status');
    const priorityFilter = document.getElementById('priority');
    const officeFilter = document.getElementById('office');
    const searchInput = document.getElementById('search');
    const form = document.getElementById('filterForm');

    // Add event listeners with null checks and event prevention
    if (statusFilter) {
        statusFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            // Small delay to prevent conflicts with other form submissions
            setTimeout(() => form.submit(), 50);
        });
    }
    
    if (priorityFilter) {
        priorityFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            setTimeout(() => form.submit(), 50);
        });
    }
    
    if (officeFilter) {
        officeFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            setTimeout(() => form.submit(), 50);
        });
    }

    // Auto-submit on search with debounce
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                form.submit();
            }, 1000); // 1 second delay
        });

        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent default form submission
                clearTimeout(searchTimeout);
                form.submit();
            }
        });
    }

    // Prevent filter form submission when clicking approve/decline buttons
    const actionButtons = document.querySelectorAll('form[action*="approve-admin"] button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Allow the button's form to submit normally
            e.stopPropagation();
        });
    });
});
</script>
@endpush

@endsection
