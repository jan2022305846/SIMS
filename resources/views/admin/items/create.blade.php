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
                                <label for="item_type" class="form-label fw-medium">Item Type *</label>
                                <select name="item_type" id="item_type" 
                                        class="form-select @error('item_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="consumable" {{ old('item_type') == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                    <option value="non_consumable" {{ old('item_type') == 'non_consumable' ? 'selected' : '' }}>Non-Consumable</option>
                                </select>
                                @error('item_type')
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
                        <div id="product-details-section" class="d-none">
                            <h5 class="mb-3">Product Details</h5>
                            <div class="row g-3">
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

                                <div class="col-md-6">
                                    <label for="unit" class="form-label fw-medium">Unit *</label>
                                    <input type="text" name="unit" id="unit" 
                                           class="form-control @error('unit') is-invalid @enderror"
                                           value="{{ old('unit', 'pcs') }}" placeholder="e.g., pcs, kg, liters" required>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="product_code" class="form-label fw-medium">Product Code/Barcode</label>
                                    <div class="input-group">
                                        <input type="text" name="product_code" id="product_code" 
                                               class="form-control @error('product_code') is-invalid @enderror"
                                               value="{{ old('product_code') }}" placeholder="Enter product code manually or scan">
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
                                    @error('product_code')
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

                                <!-- Non-consumable specific fields -->
                                <div id="non-consumable-fields" class="row g-3 d-none">
                                    <div class="col-md-6">
                                        <label for="location" class="form-label fw-medium">Location *</label>
                                        <input type="text" name="location" id="location" 
                                               class="form-control @error('location') is-invalid @enderror"
                                               value="{{ old('location') }}" required>
                                        @error('location')
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
                                        <label for="quantity" class="form-label fw-medium">Current Stock *</label>
                                        <input type="number" name="quantity" id="quantity" min="0"
                                               class="form-control @error('quantity') is-invalid @enderror"
                                               value="{{ old('quantity') }}" required>
                                        @error('quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="min_stock" class="form-label fw-medium">Minimum Stock *</label>
                                        <input type="number" name="min_stock" id="min_stock" min="0"
                                               class="form-control @error('min_stock') is-invalid @enderror"
                                               value="{{ old('min_stock') }}" required>
                                        @error('min_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="max_stock" class="form-label fw-medium">Maximum Stock</label>
                                        <input type="number" name="max_stock" id="max_stock" min="0"
                                               class="form-control @error('max_stock') is-invalid @enderror"
                                               value="{{ old('max_stock') }}">
                                        @error('max_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>                        <!-- Submit button inside form -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Create Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/create-item.js') }}"></script>
@endsection
