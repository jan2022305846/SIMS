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
                                <a href="{{ route('items.assign', $item) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-user-tag me-1"></i>
                                    {{ $item->isAssigned() ? 'Manage Assignment' : 'Assign Item' }}
                                </a>
                                <a href="{{ route('qr.download', $item) }}?type={{ $item->item_type }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-qrcode me-1"></i>
                                    Download QR
                                </a>
                            @endcan
                            @php
                                $referer = request()->headers->get('referer');
                                $backRoute = (strpos($referer, 'low-stock') !== false) ? route('items.low-stock') : route('items.index');
                                $backText = (strpos($referer, 'low-stock') !== false) ? 'Back to Low Stock' : 'Back to Items';
                            @endphp
                            <a href="{{ $backRoute }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ $backText }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        <!-- Main Information -->
                        <div class="col-lg-8">
                            
                            <!-- Stock Status Alert -->
                            @if($item instanceof \App\Models\Consumable)
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
                            @endif

                            <!-- Item Details -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Item Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <!-- Basic Information -->
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Item Name</p>
                                            <p class="h6 mb-0">{{ $item->name }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Category</p>
                                            <p class="h6 mb-0">{{ $item->category->name }}</p>
                                        </div>
                                        @if($item->description)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Description</p>
                                            <p class="mb-0">{{ $item->description }}</p>
                                        </div>
                                        @if($item->unit)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Unit of Measurement</p>
                                            <p class="h6 mb-0">{{ $item->unit }}</p>
                                        </div>
                                        @endif
                                        @endif

                                        <!-- Product Details -->
                                        @if($item->product_code)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Barcode/SKU</p>
                                            <p class="h6 mb-0 font-monospace">{{ $item->product_code }}</p>
                                        </div>
                                        @endif
                                        @if($item->brand)
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Brand</p>
                                            <p class="h6 mb-0">{{ $item->brand }}</p>
                                        </div>
                                        @endif
                                        @if($item instanceof \App\Models\NonConsumable)
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
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Management -->
                            @if(!($item instanceof \App\Models\NonConsumable))
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
                                            <div class="h4 fw-semibold text-muted mb-1">{{ number_format($item->min_stock) }}</div>
                                            <p class="text-muted small mb-0">Minimum Stock</p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="h4 fw-semibold text-muted mb-1">{{ number_format($item->max_stock ?? 0) }}</div>
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
                            @endif

                            <!-- Assignment Status -->
                            @if($item instanceof \App\Models\NonConsumable)
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-tag me-2"></i>
                                        Assignment Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($item->isAssigned())
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <p class="text-muted small mb-1">Current Holder</p>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $item->currentHolder->name }}</h6>
                                                        <small class="text-muted">{{ $item->currentHolder->email }}</small>
                                                        @if($item->currentHolder->office)
                                                            <br><small class="text-muted">{{ $item->currentHolder->office->name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="text-muted small mb-1">Assignment Details</p>
                                                <p class="mb-1"><strong>Assigned:</strong> {{ $item->updated_at->format('M d, Y g:i A') }}</p>
                                                <p class="mb-1"><strong>Duration:</strong> {{ $item->updated_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-info">Currently Assigned</span>
                                            <a href="{{ route('items.assign', $item) }}" class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="fas fa-edit me-1"></i>Update Assignment
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-user-plus fa-2x text-muted mb-2"></i>
                                            <h6 class="text-muted mb-1">Not Currently Assigned</h6>
                                            <p class="text-muted mb-3">This item is available for assignment to a user.</p>
                                            <a href="{{ route('items.assign', $item) }}" class="btn btn-primary">
                                                <i class="fas fa-user-tag me-1"></i>Assign to User
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif

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
                                        <div class="bg-light p-4 rounded mb-3 text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mb-0 mt-2">Generating QR code...</p>
                                        </div>
                                    </div>
                                    <button onclick="printQRCode()" 
                                            class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-print me-1"></i>
                                        Print QR Code
                                    </button>
                                    <p class="text-muted small mb-2">Product Code: {{ $item->product_code }}</p>
                                    @can('admin')
                                    <a href="{{ route('qr.download', $item) }}?type={{ $item->item_type }}" 
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
    // Generate QR code automatically when page loads
    generateQRCode();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function printQRCode() {
    const qrContainer = document.getElementById('qr-code-container');
    const qrImage = qrContainer.querySelector('img');
    
    if (!qrImage) {
        alert('Please generate the QR code first.');
        return;
    }
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=400,height=400');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - {{ $item->name }}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 20px;
                }
                .qr-code {
                    max-width: 300px;
                    margin: 20px auto;
                }
                .item-info {
                    margin-top: 20px;
                    font-size: 14px;
                }
                .product-code {
                    font-weight: bold;
                    color: #666;
                }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <h3>QR Code</h3>
            <div class="qr-code">
                <img src="${qrImage.src}" alt="QR Code" style="width: 100%; height: auto;">
            </div>
            <div class="item-info">
                <p><strong>Item:</strong> {{ $item->name }}</p>
                <p class="product-code">Product Code: {{ $item->product_code }}</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Wait a bit for the image to load, then print
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function generateQRCode() {
    const itemId = {{ $item->id }};
    const itemType = '{{ $item->item_type }}';

    fetch(`/qr/generate/${itemId}?type=${itemType}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('qr-code-container').innerHTML =
                `<img src="${data.qr_code}" alt="QR Code for {{ $item->name }}" class="img-fluid border rounded" style="max-width: 200px;">`;
        } else {
            document.getElementById('qr-code-container').innerHTML =
                `<div class="bg-light p-4 rounded mb-3 text-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                    <p class="text-muted mb-0">Failed to generate QR code</p>
                    <small class="text-muted">${data.message}</small>
                </div>`;
        }
    })
    .catch(error => {
        document.getElementById('qr-code-container').innerHTML =
            `<div class="bg-light p-4 rounded mb-3 text-center">
                <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                <p class="text-muted mb-0">Error generating QR code</p>
                <small class="text-muted">${error.message}</small>
            </div>`;
    });
}
</script>
@endsection
