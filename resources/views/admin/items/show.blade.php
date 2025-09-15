@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="h2 mb-1 text-dark fw-bold">{{ $item->name }}</h1>
                            <p class="text-muted mb-0">
                                <i class="fas fa-tags me-1"></i>
                                {{ $item->category->name }}
                            </p>
                        </div>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            @can('admin')
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    Edit Item
                                </a>
                                <a href="{{ route('qr.download', $item) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-qrcode me-1"></i>
                                    Download QR
                                </a>
                            @endcan
                            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Items
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <!-- Main Information -->
                        <div class="col-lg-8">
                            
                            <!-- Stock Status Alert -->
                            @if($item->isOutOfStock())
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Out of Stock</h6>
                                        <p class="mb-0 small">This item is currently out of stock.</p>
                                    </div>
                                </div>
                            @elseif($item->isLowStock())
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Low Stock Warning</h6>
                                        <p class="mb-0 small">Stock is running low. Consider restocking soon.</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Basic Information -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Basic Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Item Name</p>
                                            <p class="h6 mb-0">{{ $item->name }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Category</p>
                                            <p class="h6 mb-0">{{ $item->category->name }}</p>
                                        </div>
                                        @if($item->description)
                                        <div class="col-12">
                                            <p class="text-muted small mb-1">Description</p>
                                            <p class="mb-0">{{ $item->description }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>
                                        Product Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @if($item->barcode)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Barcode/SKU</p>
                                            <p class="h6 mb-0 font-monospace">{{ $item->barcode }}</p>
                                        </div>
                                        @endif
                                        @if($item->brand)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Brand</p>
                                            <p class="h6 mb-0">{{ $item->brand }}</p>
                                        </div>
                                        @endif
                                        @if($item->supplier)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Supplier</p>
                                            <p class="h6 mb-0">{{ $item->supplier }}</p>
                                        </div>
                                        @endif
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Location</p>
                                            <p class="h6 mb-0">{{ $item->location }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Condition</p>
                                            @if($item->condition == 'New')
                                                <span class="badge bg-success">{{ $item->condition }}</span>
                                            @elseif($item->condition == 'Good')
                                                <span class="badge bg-primary">{{ $item->condition }}</span>
                                            @elseif($item->condition == 'Fair')
                                                <span class="badge bg-warning">{{ $item->condition }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ $item->condition }}</span>
                                            @endif
                                        </div>
                                        @if($item->unit)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Unit of Measurement</p>
                                            <p class="h6 mb-0">{{ $item->unit }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Management -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-boxes me-2"></i>
                                        Stock Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4 text-center mb-4">
                                        <div class="col-md-4">
                                            <div class="h2 fw-bold mb-1 {{ $item->isOutOfStock() ? 'text-danger' : ($item->isLowStock() ? 'text-warning' : 'text-success') }}">
                                                {{ number_format($item->current_stock ?? $item->quantity) }}
                                            </div>
                                            <p class="text-muted small mb-0">Current Stock</p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="h4 fw-semibold text-muted mb-1">{{ number_format($item->minimum_stock) }}</div>
                                            <p class="text-muted small mb-0">Minimum Stock</p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="h4 fw-semibold text-muted mb-1">{{ number_format($item->maximum_stock ?? 0) }}</div>
                                            <p class="text-muted small mb-0">Maximum Stock</p>
                                        </div>
                                    </div>

                                    <!-- Stock Level Indicator -->
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted">Stock Level</span>
                                            <span class="small fw-medium">{{ number_format($item->getStockPercentage(), 1) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar {{ $item->isOutOfStock() ? 'bg-danger' : ($item->isLowStock() ? 'bg-warning' : 'bg-success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ min($item->getStockPercentage(), 100) }}%"
                                                 aria-valuenow="{{ $item->getStockPercentage() }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            @if($item->unit_price || $item->price)
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-peso-sign me-2"></i>
                                        Pricing Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @if($item->unit_price)
                                        <div class="col-md-4">
                                            <p class="text-muted small mb-1">Unit Price</p>
                                            <p class="h5 mb-0 text-success fw-bold">₱{{ number_format($item->unit_price, 2) }}</p>
                                        </div>
                                        @endif
                                        @if($item->total_value)
                                        <div class="col-md-4">
                                            <p class="text-muted small mb-1">Total Value</p>
                                            <p class="h5 mb-0 text-primary fw-bold">₱{{ number_format($item->total_value, 2) }}</p>
                                        </div>
                                        @endif
                                        @if($item->price)
                                        <div class="col-md-4">
                                            <p class="text-muted small mb-1">Legacy Price</p>
                                            <p class="h6 mb-0 text-muted">₱{{ number_format($item->price, 2) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Important Dates -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        Important Dates
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @if($item->warranty_date)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Warranty Expiry</p>
                                            <p class="h6 mb-1">{{ $item->warranty_date->format('F j, Y') }}</p>
                                            @if($item->warranty_date->isPast())
                                                <span class="badge bg-danger">Warranty Expired</span>
                                            @endif
                                        </div>
                                        @endif
                                        @if($item->expiry_date)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Product Expiry</p>
                                            <p class="h6 mb-1">{{ $item->expiry_date->format('F j, Y') }}</p>
                                            @if($item->expiry_date->isPast())
                                                <span class="badge bg-danger">Product Expired</span>
                                            @elseif($item->expiry_date->diffInDays(now()) <= 30)
                                                <span class="badge bg-warning">Expires Soon</span>
                                            @endif
                                        </div>
                                        @endif
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Created</p>
                                            <p class="h6 mb-0">{{ $item->created_at->format('F j, Y') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Last Updated</p>
                                            <p class="h6 mb-0">{{ $item->updated_at->format('F j, Y g:i A') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <!-- QR Code -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light text-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-qrcode me-2"></i>
                                        QR Code
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div id="qr-code-container">
                                        <div class="bg-light p-4 rounded mb-3">
                                            <p class="text-muted mb-0">Click to generate QR code</p>
                                        </div>
                                    </div>
                                    <button onclick="generateQRCode()" 
                                            class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-qrcode me-1"></i>
                                        Generate QR Code
                                    </button>
                                    <p class="text-muted small mb-2">QR ID: {{ $item->qr_code }}</p>
                                    @can('admin')
                                    <a href="{{ route('qr.download', $item) }}" 
                                       class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-download me-1"></i>
                                        Download QR
                                    </a>
                                    @endcan
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Quick Stats
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Status:</span>
                                        <span class="fw-semibold {{ $item->isOutOfStock() ? 'text-danger' : ($item->isLowStock() ? 'text-warning' : 'text-success') }}">
                                            {{ $item->getStockStatus() }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Total Requests:</span>
                                        <span class="fw-semibold">{{ $item->requests->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-0">
                                        <span class="text-muted">Pending Requests:</span>
                                        <span class="fw-semibold">{{ $item->requests->where('status', 'pending')->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            @can('faculty')
                            <div class="card border-light">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2"></i>
                                        Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        @if(!$item->isOutOfStock())
                                        <a href="{{ route('faculty.requests.create', ['item_id' => $item->id]) }}" 
                                           class="btn btn-success">
                                            <i class="fas fa-plus me-1"></i>
                                            Request This Item
                                        </a>
                                        @else
                                        <button disabled class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            Out of Stock
                                        </button>
                                        @endif
                                        <a href="{{ route('dashboard') }}#qr-scanner" 
                                           class="btn btn-primary">
                                            <i class="fas fa-qrcode me-1"></i>
                                            Scan QR Code
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateQRCode() {
    const itemId = {{ $item->id }};
    
    fetch(`/qr/generate/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('qr-code-container').innerHTML = 
                `<img src="${data.qr_code}" alt="QR Code for {{ $item->name }}" class="img-fluid border rounded" style="max-width: 200px;">`;
        } else {
            alert('Failed to generate QR code: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error generating QR code: ' + error.message);
    });
}
</script>
@endsection
