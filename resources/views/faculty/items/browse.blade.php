@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            Browse Items
        </h2>
        <a href="{{ route('faculty.requests.create') }}" 
           class="btn btn-warning fw-bold">
            <i class="fas fa-plus me-1"></i>
            Request Items
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Search and Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('faculty.items.index') }}" class="row g-3" id="filterForm">
                    <div class="col-md-6">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search items..." 
                               class="form-control"
                               id="searchInput">
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select" id="categorySelect">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'category']))
                                <a href="{{ route('faculty.items.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="row g-4">
            @forelse($items as $item)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <h5 class="card-title text-dark mb-2">{{ $item->name }}</h5>
                                <p class="card-text text-muted small">{{ $item->category->name ?? 'N/A' }}</p>
                            </div>
                            
                            @if($item->description)
                                <p class="card-text small mb-3">{{ Str::limit($item->description, 100) }}</p>
                            @endif

                            <div class="mb-3">
                                @if($item->item_type === 'consumable')
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Stock:</span>
                                        <span class="fw-medium small">{{ $item->quantity }} {{ $item->unit }}</span>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Status:</span>
                                        <span class="fw-medium small">
                                            @if($item->isAssigned())
                                                <span class="text-danger">Assigned</span>
                                            @else
                                                <span class="text-success">Available</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                
                                @if($item->brand)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Brand:</span>
                                        <span class="fw-medium small">{{ $item->brand }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto">
                                @if($item->item_type === 'consumable' ? $item->quantity > 0 : true)
                                    <a href="{{ route('faculty.requests.create', ['item_id' => $item->id]) }}" 
                                       class="btn btn-warning w-100 fw-bold mb-2">
                                        <i class="fas fa-plus me-1"></i>
                                        Request This Item
                                    </a>
                                @else
                                    <button disabled class="btn btn-secondary w-100 mb-2">
                                        <i class="fas fa-times me-1"></i>
                                        @if($item->item_type === 'consumable')
                                            Out of Stock
                                        @else
                                            Assigned
                                        @endif
                                    </button>
                                @endif

                                @if($item->item_type === 'consumable' && $item->isLowStock())
                                    <div class="text-center">
                                        <span class="badge bg-danger">
                                            Low Stock
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No items found</h5>
                            <p class="text-muted mb-0">Try adjusting your search criteria.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="row mt-4 mb-4">
                <div class="col-12">
                    <nav aria-label="Items pagination" class="d-flex justify-content-center w-100">
                        <div class="pagination-container">
                            {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Card alignment improvements */
.card-body {
    padding: 1.25rem;
}

.card-title {
    font-size: 1.1rem;
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

/* Pagination improvements */
nav[aria-label="Items pagination"] {
    width: 100%;
}

nav[aria-label="Items pagination"] .pagination-container {
    max-width: 600px;
    width: 100%;
}

nav[aria-label="Items pagination"] .pagination {
    margin-bottom: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 0.375rem;
    overflow: hidden;
    width: 100%;
    justify-content: space-between;
    padding: 0.25rem;
}

nav[aria-label="Items pagination"] .page-link {
    color: #495057;
    border-color: #dee2e6;
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    margin: 0 0.125rem;
    border-radius: 0.25rem !important;
}

nav[aria-label="Items pagination"] .page-link:hover {
    color: #0d6efd;
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

nav[aria-label="Items pagination"] .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
}

nav[aria-label="Items pagination"] .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #ffffff;
    border-color: #dee2e6;
}

/* Responsive pagination */
@media (max-width: 576px) {
    nav[aria-label="Items pagination"] {
        max-width: 100%;
    }
    
    nav[aria-label="Items pagination"] .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
    }
    
    nav[aria-label="Items pagination"] .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        margin: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');

    let searchTimeout;

    // Auto-submit on category changes
    categorySelect.addEventListener('change', function() {
        if (searchInput.value.trim() || categorySelect.selectedIndex > 1) {
            filterForm.submit();
        }
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
        const categoryVal = categorySelect.value;

        // If all fields are empty, redirect to clean URL
        if (!searchVal && !categoryVal) {
            e.preventDefault();
            window.location.href = '{{ route("faculty.items.index") }}';
            return false;
        }
    });
});
</script>
@endpush
