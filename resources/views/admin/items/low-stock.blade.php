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
                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                            Low Stock Items
                        </h2>
                        <p class="text-muted mb-0">Items that need immediate restocking</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary fw-bold">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Items
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
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                        <h4 class="mb-1">{{ $items->total() }}</h4>
                                        <small class="text-muted">Low Stock Items</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-body border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                                        <h4 class="mb-1">
                                            {{ $items->sum('quantity') }}
                                        </h4>
                                        <small class="text-muted">Total Stock Remaining</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-body border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                        <h4 class="mb-1">
                                            {{ $items->avg(function($item) {
                                                return $item->minimum_stock > 0 ? ($item->current_stock / $item->minimum_stock) * 100 : 0;
                                            }) ? round($items->avg(function($item) {
                                                return $item->minimum_stock > 0 ? ($item->current_stock / $item->minimum_stock) * 100 : 0;
                                            })) : 0 }}%
                                        </h4>
                                        <small class="text-muted">Avg Stock Level</small>
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
                                            <i class="fas fa-boxes me-1"></i>Current Stock
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-minus me-1"></i>Minimum Stock
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-percentage me-1"></i>Stock Status
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-cogs me-1"></i>Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        @php
                                            $stockPercentage = $item->minimum_stock > 0 ? ($item->current_stock / $item->minimum_stock) * 100 : 0;
                                            $stockStatus = $stockPercentage <= 25 ? 'critical' : 'low';
                                        @endphp
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
                                                    <span class="fw-semibold me-2">{{ $item->current_stock }}</span>
                                                    @if($item->current_stock <= 0)
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    @elseif($item->current_stock <= ($item->minimum_stock ?? 10))
                                                        <span class="badge bg-warning">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-success">In Stock</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $item->minimum_stock ?? 'Not set' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    if ($item->current_stock <= 0) {
                                                        $status = "Out of stock";
                                                    } elseif ($item->current_stock < ($item->minimum_stock ?? 10)) {
                                                        $unitsNeeded = ($item->minimum_stock ?? 10) - $item->current_stock;
                                                        $status = "Needs {$unitsNeeded} more";
                                                    } else {
                                                        $status = "At minimum level";
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $item->current_stock }} units</span>
                                                    <small class="text-muted">{{ $status }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('items.show', $item->id) }}?type={{ $item->item_type }}"
                                                       class="btn btn-outline-info"
                                                       data-bs-toggle="tooltip"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('items.edit', $item->id) }}?type={{ $item->item_type }}"
                                                       class="btn btn-outline-warning"
                                                       data-bs-toggle="tooltip"
                                                       title="Edit Item">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($item->current_stock > 0)
                                                        <button class="btn btn-outline-success"
                                                                data-bs-toggle="tooltip"
                                                                title="Restock Item"
                                                                onclick="showRestockModal({{ $item->id }}, '{{ $item->name }}')">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-body rounded-circle p-4 mb-3">
                                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                                    </div>
                                                    <h5 class="text-success mb-1">All Items Well Stocked</h5>
                                                    <p class="text-muted mb-0">No items are currently running low on stock.</p>
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
                                    Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} low stock items
                                </div>
                                <nav aria-label="Items pagination">
                                    {{ $items->links('pagination::bootstrap-5') }}
                                </nav>
                            </div>
                        @else
                            @if($items->total() > 0)
                                <div class="text-center mt-4 text-muted">
                                    Showing all {{ $items->total() }} low stock items
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restockModalLabel">Restock Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restockForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="restock_quantity" class="form-label">Additional Quantity</label>
                        <input type="number" class="form-control" id="restock_quantity" name="additional_quantity" min="1" required>
                        <div class="form-text">Enter the quantity to add to current stock</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock: <span id="currentStock">0</span></label>
                        <label class="form-label">New Total: <span id="newTotal">0</span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Restock Item</button>
                </div>
            </form>
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

// Restock modal functionality
function showRestockModal(itemId, itemName) {
    document.getElementById('restockModalLabel').textContent = 'Restock: ' + itemName;
    document.getElementById('restockForm').action = `/items/${itemId}/restock`;

    // You would need to fetch current stock here, for now using placeholder
    document.getElementById('currentStock').textContent = 'Loading...';
    document.getElementById('newTotal').textContent = 'Loading...';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('restockModal'));
    modal.show();

    // Update calculations when quantity changes
    document.getElementById('restock_quantity').addEventListener('input', function() {
        const additional = parseInt(this.value) || 0;
        const current = parseInt(document.getElementById('currentStock').textContent) || 0;
        document.getElementById('newTotal').textContent = current + additional;
    });
}
</script>
@endsection