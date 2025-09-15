@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-box me-2 text-warning"></i>
                        Item Management
                    </h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('items.create') }}" 
                           class="btn btn-warning fw-bold">
                            <i class="fas fa-plus me-1"></i>
                            Add New Item
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
                                <div class="col-md-4">
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
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <select class="form-select" name="stock">
                                        <option value="">All Stock</option>
                                        <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                        <option value="in-stock" {{ request('stock') == 'in-stock' ? 'selected' : '' }}>In Stock</option>
                                        <option value="out-of-stock" {{ request('stock') == 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>
                                        Filter
                                    </button>
                                </div>
                            </div>
                            @if(request()->hasAny(['search', 'category', 'stock']))
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
                                            <i class="fas fa-peso-sign me-1"></i>Price
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
                                                <div class="fw-semibold text-dark">{{ $item->name }}</div>
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
                                            @elseif($item->quantity <= ($item->minimum_stock ?? 10))
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
                                        <span class="fw-semibold text-success">
                                            {{ $item->price ? '₱' . number_format($item->price, 2) : 'N/A' }}
                                        </span>
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
                                    <td colspan="6" class="text-center text-muted py-5">
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
                            {{ $items->links('pagination::bootstrap-4') }}
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-submit form on filter change
    const categoryFilter = document.querySelector('select[name="category"]');
    const stockFilter = document.querySelector('select[name="stock"]');
    const searchInput = document.querySelector('input[name="search"]');
    const form = document.getElementById('searchForm'); // Target specific form
    const loadingOverlay = document.getElementById('loading-overlay');

    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.remove('d-none');
        }
    }

    // Check if form exists
    if (!form) {
        console.error('Search form not found!');
        return;
    }

    // Auto-submit on select change
    categoryFilter.addEventListener('change', function() {
        console.log('Category filter changed to:', this.value);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        showLoading();
        form.submit();
    });

    stockFilter.addEventListener('change', function() {
        console.log('Stock filter changed to:', this.value);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        showLoading();
        form.submit();
    });

    // Auto-submit on search with debounce
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            showLoading();
            form.submit();
        }, 1000); // 1 second delay
    });

    // Submit on Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            showLoading();
            form.submit();
        }
    });
});
</script>
@endsection
