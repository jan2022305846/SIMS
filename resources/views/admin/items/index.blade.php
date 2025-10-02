@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 fw-semibold text-dark mb-0">
                    <i class="fas fa-box me-2 text-warning"></i>
                    Item Management
                </h2>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('items.create') }}" 
                       class="btn btn-warning fw-bold">
                        <i class="fas fa-plus me-1"></i>
                        Add New Item
                    </a>
                    <a href="{{ route('items.trashed') }}" 
                       class="btn btn-secondary fw-bold">
                        <i class="fas fa-trash me-1"></i>
                        Trash
                    </a>
                    <a href="{{ route('items.low-stock') }}" 
                       class="btn btn-danger fw-bold">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Low Stock
                    </a>
                </div>
            </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Search and Filter -->
                        <form id="searchForm" method="GET" action="{{ route('items.index') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               name="search" 
                                               placeholder="Search items..."
                                               value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="category">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="type">
                                        <option value="">All Types</option>
                                        <option value="consumable" {{ request('type') == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                        <option value="non_consumable" {{ request('type') == 'non_consumable' ? 'selected' : '' }}>Non-Consumable</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>
                                        Filter
                                    </button>
                                </div>
                            </div>
                            @if(request()->hasAny(['search', 'category', 'type']))
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-times me-1"></i>
                                                Clear Filters
                                            </a>
                                            @if($items->total() > 0)
                                                <span class="text-muted small">
                                                    {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} items
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>

                        <!-- Items Table -->
                        <div class="table-responsive position-relative">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">
                                            <i class="fas fa-tag me-1"></i>Item Details
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-folder me-1"></i>Category
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-boxes me-1"></i>Stock
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-ruler me-1"></i>Unit
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-cogs me-1"></i>Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="fas fa-box text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->name }}</div>
                                                <div class="text-muted small">{{ $item->brand ?? 'No brand specified' }}</div>
                                                @if($item->description)
                                                    <div class="text-muted small">{{ Str::limit($item->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $item->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-semibold me-2">{{ $item->quantity }}</span>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->quantity <= $item->min_stock)
                                                <span class="badge bg-warning">Low Stock</span>
                                            @else
                                                <span class="badge bg-success">In Stock</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $item->unit }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('items.show', $item) }}" 
                                               class="btn btn-outline-info"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('items.edit', $item) }}" 
                                               class="btn btn-outline-warning"
                                               data-bs-toggle="tooltip"
                                               title="Edit Item">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('items.destroy', $item) }}" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this item?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="Delete Item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-light rounded-circle p-4 mb-3">
                                                <i class="fas fa-box fa-3x text-muted"></i>
                                            </div>
                                            <h5 class="text-muted mb-1">No Items Found</h5>
                                            <p class="text-muted mb-0">Start by adding your first item to the inventory.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Loading overlay -->
                    <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.8); z-index: 1000;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 text-muted">Filtering items...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($items->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} items
                        </div>
                        <nav aria-label="Items pagination">
                            {{ $items->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @else
                    @if($items->total() > 0)
                        <div class="text-center mt-4 text-muted">
                            Showing all {{ $items->total() }} items
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-submit form on select change - with protection against conflicts
    const categoryFilter = document.getElementById('category');
    const typeFilter = document.getElementById('type');
    const stockFilter = document.getElementById('stock');
    const searchInput = document.getElementById('search');
    const form = document.getElementById('searchForm');

    // Add event listeners with null checks and event prevention
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            // Small delay to prevent conflicts with other form submissions
            setTimeout(() => form.submit(), 50);
        });
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            setTimeout(() => form.submit(), 50);
        });
    }

    if (stockFilter) {
        stockFilter.addEventListener('change', function(e) {
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

    // Show loading spinner function
    function showLoading() {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
        loadingOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        loadingOverlay.style.zIndex = '9999';
        loadingOverlay.innerHTML = `
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }

    // Add loading to filter form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            showLoading();
            form.submit();
        }
    });
});
</script>
@endsection
@endsection
