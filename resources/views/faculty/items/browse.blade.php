@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            Browse Items
        </h2>
        <a href="{{ route('requests.create') }}" 
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
                <form method="GET" action="{{ route('items.browse') }}" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search items..." 
                               class="form-control">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Items Grid -->
        <div class="row g-4">
            @forelse($items as $item)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="card-title text-dark mb-2">{{ $item->name }}</h5>
                                <p class="card-text text-muted small">{{ $item->category->name ?? 'N/A' }}</p>
                            </div>
                            
                            @if($item->description)
                                <p class="card-text small mb-3">{{ Str::limit($item->description, 100) }}</p>
                            @endif

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Quantity:</span>
                                    <span class="fw-medium small">{{ $item->quantity }} {{ $item->unit }}</span>
                                </div>
                                
                                @if($item->brand)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Brand:</span>
                                        <span class="fw-medium small">{{ $item->brand }}</span>
                                    </div>
                                @endif

                                @if($item->price)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Price:</span>
                                        <span class="fw-medium small">₱{{ number_format($item->price, 2) }}</span>
                                    </div>
                                @endif

                                @if($item->expiry_date)
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Expires:</span>
                                        <span class="fw-medium small">{{ $item->expiry_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto">
                                @if($item->quantity > 0)
                                    <a href="{{ route('requests.create', ['item_id' => $item->id]) }}" 
                                       class="btn btn-warning w-100 fw-bold">
                                        <i class="fas fa-plus me-1"></i>
                                        Request This Item
                                    </a>
                                @else
                                    <button disabled class="btn btn-secondary w-100">
                                        <i class="fas fa-times me-1"></i>
                                        Out of Stock
                                    </button>
                                @endif
                            </div>

                            @if($item->quantity <= ($item->minimum_stock ?? 10))
                                <div class="mt-2">
                                    <span class="badge bg-danger">
                                        Low Stock
                                    </span>
                                </div>
                            @endif
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
                                </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Items pagination" class="d-flex justify-content-center">
                        {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
    </div>
</div>
@endsection
