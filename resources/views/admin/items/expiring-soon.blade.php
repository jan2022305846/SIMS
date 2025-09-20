@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <div>
                        <h2 class="h3 fw-semibold mb-1">
                            <i class="fas fa-calendar-times me-2 text-danger"></i>
                            Items Expiring Soon
                        </h2>
                        <p class="text-muted mb-0">Items that will expire within 30 days</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary fw-bold">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Items
                        </a>
                        <a href="{{ route('items.create') }}" class="btn btn-warning fw-bold">
                            <i class="fas fa-plus me-1"></i>
                            Add New Item
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Summary Stats -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="card bg-body border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-times fa-2x text-danger mb-2"></i>
                                        <h4 class="mb-1">{{ $items->total() }}</h4>
                                        <small class="text-muted">Items Expiring Soon</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-body border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                        <h4 class="mb-1">
                                            {{ $items->where('expiry_date', '<=', now())->count() }}
                                        </h4>
                                        <small class="text-muted">Already Expired</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-body border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                        <h4 class="mb-1">
                                            {{ $items->where('expiry_date', '>', now())->avg(function($item) {
                                                return now()->diffInDays($item->expiry_date);
                                            }) ? round($items->where('expiry_date', '>', now())->avg(function($item) {
                                                return now()->diffInDays($item->expiry_date);
                                            })) : 0 }}
                                        </h4>
                                        <small class="text-muted">Avg Days Left</small>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                            <i class="fas fa-calendar-times me-1"></i>Expiry Date
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-clock me-1"></i>Days Left
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
                                                <span class="fw-semibold {{ $item->expiry_date <= now() ? 'text-danger' : '' }}">
                                                    {{ $item->expiry_date ? $item->expiry_date->format('M j, Y') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $daysLeft = $item->expiry_date ? now()->diffInDays($item->expiry_date, false) : null;
                                                @endphp
                                                @if($daysLeft === null)
                                                    <span class="text-muted">N/A</span>
                                                @elseif($daysLeft <= 0)
                                                    <span class="badge bg-danger">Expired</span>
                                                @elseif($daysLeft <= 7)
                                                    <span class="badge bg-danger">{{ $daysLeft }} days</span>
                                                @elseif($daysLeft <= 14)
                                                    <span class="badge bg-warning">{{ $daysLeft }} days</span>
                                                @else
                                                    <span class="badge bg-info">{{ $daysLeft }} days</span>
                                                @endif
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
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-body rounded-circle p-4 mb-3">
                                                        <i class="fas fa-calendar-check fa-3x text-success"></i>
                                                    </div>
                                                    <h5 class="text-success mb-1">No Items Expiring Soon</h5>
                                                    <p class="text-muted mb-0">All items are safe for at least 30 days.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($items->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} expiring items
                                </div>
                                <nav aria-label="Items pagination">
                                    {{ $items->links('pagination::bootstrap-4') }}
                                </nav>
                            </div>
                        @else
                            @if($items->total() > 0)
                                <div class="text-center mt-4 text-muted">
                                    Showing all {{ $items->total() }} expiring items
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
});
</script>
@endsection