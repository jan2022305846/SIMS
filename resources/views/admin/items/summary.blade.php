@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-semibold text-dark mb-1">
                        <i class="fas fa-list-ul me-2 text-primary"></i>
                        Items Availability Summary
                    </h2>
                    <p class="text-muted small mb-0">
                        Comprehensive overview of item availability and stock levels
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-table me-1"></i>
                        Full Inventory
                    </a>
                    <a href="{{ route('items.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Add Item
                    </a>
                </div>
            </div>

            <!-- Summary Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-boxes text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Total Items</h5>
                                    <h3 class="mb-0">{{ number_format($totalItems) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-check-circle text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Available</h5>
                                    <h3 class="mb-0 text-success">{{ number_format($availableItems) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Low Stock</h5>
                                    <h3 class="mb-0 text-warning">{{ number_format($lowStockItems) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-danger bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-times-circle text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Out of Stock</h5>
                                    <h3 class="mb-0 text-danger">{{ number_format($outOfStockItems) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Stock Value Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fas fa-boxes fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.8rem;">Inventory Overview</h5>
                                    <h2 class="mb-0 text-white">{{ number_format($totalItems) }} Items</h2>
                                    <small class="text-white-50">Total items in inventory</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('items.summary') }}" class="row g-3">
                        <!-- Search -->
                        <div class="col-lg-4">
                            <label for="search" class="form-label small fw-medium">Search Items</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Search by name, brand, location..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-lg-2">
                            <label for="category_id" class="form-label small fw-medium">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status Filter -->
                        <div class="col-lg-2">
                            <label for="stock_status" class="form-label small fw-medium">Stock Status</label>
                            <select name="stock_status" id="stock_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                <option value="critical" {{ request('stock_status') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>

                        <!-- Location Filter -->
                        <div class="col-lg-2">
                            <label for="location" class="form-label small fw-medium">Location</label>
                            <select name="location" id="location" class="form-select">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" 
                                            {{ request('location') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-lg-2">
                            <label class="form-label small fw-medium">&nbsp;</label>
                            <div class="d-grid gap-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('items.summary') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Items List
                            <span class="badge bg-primary ms-2">{{ $items->total() }} items</span>
                        </h5>
                        
                        <!-- Sorting Options -->
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="btnradio" id="sortName" autocomplete="off" 
                                       {{ request('sort') == 'name' || !request('sort') ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm" for="sortName">
                                    <i class="fas fa-sort-alpha-down me-1"></i>Name
                                </label>

                                <input type="radio" class="btn-check" name="btnradio" id="sortStock" autocomplete="off"
                                       {{ request('sort') == 'current_stock' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm" for="sortStock">
                                    <i class="fas fa-sort-numeric-down me-1"></i>Stock
                                </label>

                                <input type="radio" class="btn-check" name="btnradio" id="sortLocation" autocomplete="off"
                                       {{ request('sort') == 'location' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm" for="sortLocation">
                                    <i class="fas fa-map-marker-alt me-1"></i>Location
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Details</th>
                                        <th>Category</th>
                                        <th>Stock Status</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        @if($item->current_stock <= 0)
                                                            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-times"></i>
                                                            </div>
                                                        @elseif($item->current_stock <= $item->minimum_stock)
                                                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-exclamation"></i>
                                                            </div>
                                                        @else
                                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="fas fa-check"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">
                                                            <a href="{{ route('items.show', $item) }}" 
                                                               class="text-decoration-none text-dark">
                                                                {{ $item->name }}
                                                            </a>
                                                        </h6>
                                                        @if($item->brand)
                                                            <small class="text-muted">{{ $item->brand }}</small>
                                                        @endif
                                                        @if($item->description)
                                                            <small class="text-muted d-block">{{ Str::limit($item->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $item->category->name }}</span>
                                            </td>
                                            
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <h6 class="mb-0 fw-bold {{ $item->current_stock <= 0 ? 'text-danger' : ($item->current_stock <= $item->minimum_stock ? 'text-warning' : 'text-success') }}">
                                                            {{ number_format($item->current_stock) }}
                                                        </h6>
                                                        <small class="text-muted">Available</small>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        @php
                                                            $percentage = $item->maximum_stock > 0 ? ($item->current_stock / $item->maximum_stock) * 100 : 0;
                                                            $progressClass = $item->current_stock <= 0 ? 'bg-danger' : ($item->current_stock <= $item->minimum_stock ? 'bg-warning' : 'bg-success');
                                                        @endphp
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar {{ $progressClass }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ min($percentage, 100) }}%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">Min: {{ number_format($item->minimum_stock) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                {{ $item->location }}
                                            </td>
                                            
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('items.show', $item) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('items.edit', $item) }}" 
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No items found</h5>
                            <p class="text-muted">Try adjusting your search criteria or filters.</p>
                            <a href="{{ route('items.summary') }}" class="btn btn-primary">
                                <i class="fas fa-refresh me-1"></i>Reset Filters
                            </a>
                        </div>
                    @endif
                </div>

                @if($items->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} items
                            </div>
                            {{ $items->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Sort functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="btnradio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const sortField = this.id.replace('sort', '').toLowerCase();
            const url = new URL(window.location);
            
            switch(sortField) {
                case 'name':
                    url.searchParams.set('sort', 'name');
                    break;
                case 'stock':
                    url.searchParams.set('sort', 'current_stock');
                    break;
                case 'location':
                    url.searchParams.set('sort', 'location');
                    break;
            }
            
            window.location.href = url.toString();
        });
    });
});
</script>
@endsection
