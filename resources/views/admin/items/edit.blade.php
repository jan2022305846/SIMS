@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h1 class="h3 mb-0 text-dark fw-bold">
                            <i class="fas fa-edit me-2"></i>
                            Edit Item: {{ $item->name }}
                        </h1>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <a href="{{ route('qr.download', $item) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-qrcode me-1"></i>
                                Download QR
                            </a>
                            <a href="{{ route('items.show', $item) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-eye me-1"></i>
                                View Item
                            </a>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Items
                            </a>
                        </div>
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

                    <form action="{{ route('items.update', $item) }}" method="POST" id="item-edit-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Item Name *</label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $item->name) }}" required>
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
                                        <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
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
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description) }}</textarea>
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
                                <input type="text" name="barcode" id="barcode" 
                                       class="form-control @error('barcode') is-invalid @enderror"
                                       value="{{ old('barcode', $item->barcode) }}">
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="brand" class="form-label fw-medium">Brand</label>
                                <input type="text" name="brand" id="brand" 
                                       class="form-control @error('brand') is-invalid @enderror"
                                       value="{{ old('brand', $item->brand) }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="supplier" class="form-label fw-medium">Supplier</label>
                                <input type="text" name="supplier" id="supplier" 
                                       class="form-control @error('supplier') is-invalid @enderror"
                                       value="{{ old('supplier', $item->supplier) }}">
                                @error('supplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label fw-medium">Location *</label>
                                <input type="text" name="location" id="location" 
                                       class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location', $item->location) }}" required>
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
                                               value="{{ old('current_stock', $item->current_stock ?? $item->quantity) }}" required>
                                        @error('current_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="minimum_stock" class="form-label fw-medium">Minimum Stock *</label>
                                        <input type="number" name="minimum_stock" id="minimum_stock" min="0"
                                               class="form-control @error('minimum_stock') is-invalid @enderror"
                                               value="{{ old('minimum_stock', $item->minimum_stock) }}" required>
                                        @error('minimum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="maximum_stock" class="form-label fw-medium">Maximum Stock</label>
                                        <input type="number" name="maximum_stock" id="maximum_stock" min="0"
                                               class="form-control @error('maximum_stock') is-invalid @enderror"
                                               value="{{ old('maximum_stock', $item->maximum_stock) }}">
                                        @error('maximum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="unit" class="form-label fw-medium">Unit of Measurement</label>
                                        <select name="unit" id="unit" 
                                                class="form-select @error('unit') is-invalid @enderror">
                                            <option value="">Select Unit</option>
                                            <option value="pieces" {{ old('unit', $item->unit) == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                            <option value="boxes" {{ old('unit', $item->unit) == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                            <option value="reams" {{ old('unit', $item->unit) == 'reams' ? 'selected' : '' }}>Reams</option>
                                            <option value="bottles" {{ old('unit', $item->unit) == 'bottles' ? 'selected' : '' }}>Bottles</option>
                                            <option value="packs" {{ old('unit', $item->unit) == 'packs' ? 'selected' : '' }}>Packs</option>
                                            <option value="liters" {{ old('unit', $item->unit) == 'liters' ? 'selected' : '' }}>Liters</option>
                                            <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Kilograms</option>
                                        </select>
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="condition" class="form-label fw-medium">Condition *</label>
                                        <select name="condition" id="condition" 
                                                class="form-select @error('condition') is-invalid @enderror" required>
                                            <option value="New" {{ old('condition', $item->condition) == 'New' ? 'selected' : '' }}>New</option>
                                            <option value="Good" {{ old('condition', $item->condition) == 'Good' ? 'selected' : '' }}>Good</option>
                                            <option value="Fair" {{ old('condition', $item->condition) == 'Fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="Needs Repair" {{ old('condition', $item->condition) == 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <hr class="my-4">
                        <div class="card border-light">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-peso-sign me-2"></i>
                                    Pricing Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="unit_price" class="form-label fw-medium">Unit Price (₱)</label>
                                        <input type="number" name="unit_price" id="unit_price" min="0" step="0.01"
                                               class="form-control @error('unit_price') is-invalid @enderror"
                                               value="{{ old('unit_price', $item->unit_price) }}" onchange="calculateTotal()">
                                        @error('unit_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="price" class="form-label fw-medium">Legacy Price (₱)</label>
                                        <input type="number" name="price" id="price" min="0"
                                               class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', $item->price) }}">
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="total_value" class="form-label fw-medium">Total Value (₱)</label>
                                        <input type="number" name="total_value" id="total_value" min="0" step="0.01" readonly
                                               class="form-control-plaintext bg-light border"
                                               value="{{ old('total_value', $item->total_value) }}">
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
                                       value="{{ old('warranty_date', $item->warranty_date?->format('Y-m-d')) }}">
                                @error('warranty_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label fw-medium">Product Expiry Date</label>
                                <input type="date" name="expiry_date" id="expiry_date" 
                                       class="form-control @error('expiry_date') is-invalid @enderror"
                                       value="{{ old('expiry_date', $item->expiry_date?->format('Y-m-d')) }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <hr class="my-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary bg-opacity-10">
                                <h5 class="card-title mb-0 text-primary">
                                    <i class="fas fa-qrcode me-2"></i>
                                    QR Code Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            Current QR Code: <span class="fw-bold text-primary">{{ $item->qr_code }}</span>
                                        </p>
                                        <p class="text-muted small mb-0">QR codes are automatically generated when the item is saved.</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div id="qr-preview" class="mb-3"></div>
                                        <button type="button" onclick="generateQRPreview()" 
                                                class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            Preview QR Code
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" form="item-edit-form" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Update Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const currentStock = parseFloat(document.getElementById('current_stock').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const totalValue = currentStock * unitPrice;
    document.getElementById('total_value').value = totalValue.toFixed(2);
}

function generateQRPreview() {
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
            document.getElementById('qr-preview').innerHTML = 
                `<img src="${data.qr_code}" alt="QR Code" class="img-thumbnail" style="width: 120px; height: 120px;">`;
        } else {
            alert('Failed to generate QR code: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error generating QR code: ' + error.message);
    });
}

// Auto-calculate total when values change
document.getElementById('current_stock').addEventListener('input', calculateTotal);
document.getElementById('unit_price').addEventListener('input', calculateTotal);

// Calculate on page load
document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection
