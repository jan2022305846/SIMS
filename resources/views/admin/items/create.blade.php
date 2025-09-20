@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0 text-dark fw-bold">
                            <i class="fas fa-plus-circle me-2"></i>
                            Add New Item
                        </h1>
                        <a href="{{ route('items.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Items
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Please correct the following errors:
                            </h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('items.store') }}" method="POST" id="item-form">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Item Name *</label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-medium">Category *</label>
                                <select name="category_id" id="category_id" 
                                        class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-medium">Description</label>
                                <textarea name="description" id="description" rows="3"
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Product Details -->
                        <hr class="my-4">
                        <h5 class="mb-3">Product Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="barcode" class="form-label fw-medium">Barcode/SKU</label>
                                <div class="input-group">
                                    <input type="text" name="barcode" id="barcode" 
                                           class="form-control @error('barcode') is-invalid @enderror"
                                           value="{{ old('barcode') }}" placeholder="Enter barcode manually or scan">
                                    <button type="button" class="btn btn-outline-primary" id="scan-barcode-btn" title="Scan Barcode">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="manual-barcode-btn" title="Manual Entry">
                                        <i class="fas fa-keyboard"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Use barcode scanner or enter manually
                                    </small>
                                </div>
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="brand" class="form-label fw-medium">Brand</label>
                                <input type="text" name="brand" id="brand" 
                                       class="form-control @error('brand') is-invalid @enderror"
                                       value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="supplier" class="form-label fw-medium">Supplier</label>
                                <input type="text" name="supplier" id="supplier" 
                                       class="form-control @error('supplier') is-invalid @enderror"
                                       value="{{ old('supplier') }}">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label fw-medium">Location *</label>
                                <input type="text" name="location" id="location" 
                                       class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location') }}" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock Management -->
                        <hr class="my-4">
                        <div class="card border-light">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-boxes me-2"></i>
                                    Stock Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="current_stock" class="form-label fw-medium">Current Stock *</label>
                                        <input type="number" name="current_stock" id="current_stock" min="0"
                                               class="form-control @error('current_stock') is-invalid @enderror"
                                               value="{{ old('current_stock', 0) }}" required>
                                        @error('current_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="minimum_stock" class="form-label fw-medium">Minimum Stock *</label>
                                        <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                               class="form-control @error('minimum_stock') is-invalid @enderror"
                                               value="{{ old('minimum_stock', 1) }}" required>
                                        @error('minimum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="maximum_stock" class="form-label fw-medium">Maximum Stock</label>
                                        <input type="number" name="maximum_stock" id="maximum_stock" min="0"
                                               class="form-control @error('maximum_stock') is-invalid @enderror"
                                               value="{{ old('maximum_stock', 100) }}">
                                        @error('maximum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="unit" class="form-label fw-medium">Unit of Measurement</label>
                                        <select name="unit" id="unit" 
                                                class="form-select @error('unit') is-invalid @enderror">
                                            <option value="">Select Unit</option>
                                            <option value="pieces" {{ old('unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                            <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                            <option value="reams" {{ old('unit') == 'reams' ? 'selected' : '' }}>Reams</option>
                                            <option value="bottles" {{ old('unit') == 'bottles' ? 'selected' : '' }}>Bottles</option>
                                            <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>Packs</option>
                                            <option value="liters" {{ old('unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms</option>
                                        </select>
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="condition" class="form-label fw-medium">Condition *</label>
                                        <select name="condition" id="condition" 
                                                class="form-select @error('condition') is-invalid @enderror" required>
                                            <option value="New" {{ old('condition') == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Good" {{ old('condition', 'Good') == 'Good' ? 'selected' : '' }}>Good</option>
                                            <option value="Fair" {{ old('condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="Needs Repair" {{ old('condition') == 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <hr class="my-4">
                        <h5 class="mb-3">Date Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="warranty_date" class="form-label fw-medium">Warranty Expiry Date</label>
                                <input type="date" name="warranty_date" id="warranty_date" 
                                       class="form-control @error('warranty_date') is-invalid @enderror"
                                       value="{{ old('warranty_date') }}">
                                @error('warranty_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label fw-medium">Product Expiry Date</label>
                                <input type="date" name="expiry_date" id="expiry_date" 
                                       class="form-control @error('expiry_date') is-invalid @enderror"
                                       value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('items.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" form="item-form" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Create Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Include QuaggaJS library for barcode scanning
document.addEventListener('DOMContentLoaded', function() {
    // Load QuaggaJS dynamically
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
    script.onload = function() {
        initializeBarcodeScanner();
    };
    document.head.appendChild(script);
});

function initializeBarcodeScanner() {
    let scannerActive = false;
    let scannerContainer = null;

    const scanBtn = document.getElementById('scan-barcode-btn');
    const manualBtn = document.getElementById('manual-barcode-btn');
    const barcodeInput = document.getElementById('barcode');

    // Scan barcode button
    scanBtn.addEventListener('click', function() {
        if (scannerActive) {
            stopScanner();
        } else {
            startScanner();
        }
    });

    // Manual entry button
    manualBtn.addEventListener('click', function() {
        stopScanner();
        barcodeInput.focus();
        barcodeInput.placeholder = "Enter barcode manually";
    });

    function startScanner() {
        scannerActive = true;
        
        // Create scanner container
        scannerContainer = document.createElement('div');
        scannerContainer.id = 'barcode-scanner-container';
        scannerContainer.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            width: 400px;
            max-width: 90vw;
        `;

        scannerContainer.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Barcode Scanner</h5>
                <button type="button" class="btn-close" id="close-scanner"></button>
            </div>
            <div id="scanner-viewport" style="width: 100%; height: 300px; border: 1px solid #ddd;"></div>
            <div class="mt-3 text-center">
                <small class="text-muted">Position barcode in front of camera</small>
            </div>
        `;

        document.body.appendChild(scannerContainer);

        // Initialize Quagga
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner-viewport'),
                constraints: {
                    width: 640,
                    height: 480,
                    facingMode: "environment" // Use back camera on mobile
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: 2,
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "codabar_reader"
                ]
            },
            locate: true
        }, function(err) {
            if (err) {
                console.error(err);
                alert('Error initializing camera: ' + err.message);
                stopScanner();
                return;
            }
            Quagga.start();
        });

        // Handle barcode detection
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            barcodeInput.value = code;
            stopScanner();
            
            // Show success message
            showToast('Barcode scanned successfully!', 'success');
        });

        // Close scanner button
        document.getElementById('close-scanner').addEventListener('click', stopScanner);
    }

    function stopScanner() {
        scannerActive = false;
        
        if (scannerContainer) {
            document.body.removeChild(scannerContainer);
            scannerContainer = null;
        }
        
        if (typeof Quagga !== 'undefined') {
            Quagga.stop();
        }
        
        scanBtn.innerHTML = '<i class="fas fa-qrcode"></i>';
        scanBtn.classList.remove('btn-danger');
        scanBtn.classList.add('btn-outline-primary');
        scanBtn.title = 'Scan Barcode';
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
        `;
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
}
</script>
@endsection
