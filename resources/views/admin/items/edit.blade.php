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
                            <a href="{{ route('items.show', $item->id) }}?type={{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}" class="btn btn-success btn-sm">
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

                    <form action="{{ route('items.update', $item) }}?type={{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}" method="POST" id="item-edit-form">
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
                                <div class="position-relative">
                                    <select name="category_id" id="category_id" 
                                            class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                </div>
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
                                <label for="unit" class="form-label fw-medium">Unit *</label>
                                <input type="text" name="unit" id="unit" 
                                       class="form-control @error('unit') is-invalid @enderror"
                                       value="{{ old('unit', $item->unit) }}" placeholder="e.g., pcs, kg, liters" required>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="product_code" class="form-label fw-medium">Product Code/Barcode</label>
                                <input type="text" name="product_code" id="product_code" 
                                       class="form-control @error('product_code') is-invalid @enderror"
                                       value="{{ old('product_code', $item->product_code) }}">
                                @error('product_code')
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

                            @if($item instanceof \App\Models\NonConsumable)
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-medium">Location *</label>
                                <div class="position-relative">
                                    <select name="location" id="location"
                                            class="form-select @error('location') is-invalid @enderror" required>
                                        <option value="">Select Location</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->name }}" {{ old('location', $item->location) == $office->name ? 'selected' : '' }}>
                                                {{ $office->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                </div>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="condition" class="form-label fw-medium">Condition *</label>
                                <div class="position-relative">
                                    <select name="condition" id="condition" 
                                            class="form-select @error('condition') is-invalid @enderror" required>
                                        <option value="New" {{ old('condition', $item->condition) == 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Good" {{ old('condition', $item->condition) == 'Good' ? 'selected' : '' }}>Good</option>
                                        <option value="Fair" {{ old('condition', $item->condition) == 'Fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="Needs Repair" {{ old('condition', $item->condition) == 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                </div>
                                @error('condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>

                        <!-- Stock Management -->
                        @if($item instanceof \App\Models\Consumable)
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
                                    <!-- First Row: Current Stock and Add Stock -->
                                    <div class="col-md-6">
                                        <label for="current_stock_display" class="form-label fw-medium">Current Stock</label>
                                        <input type="text" id="current_stock_display"
                                               class="form-control bg-light"
                                               value="{{ $item->quantity }} {{ $item->unit ?? 'pcs' }}" readonly>
                                        <small class="text-muted">Current stock level (read-only)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="add_quantity" class="form-label fw-medium">Add Stock</label>
                                        <input type="number" name="add_quantity" id="add_quantity" min="0"
                                               class="form-control @error('add_quantity') is-invalid @enderror"
                                               value="{{ old('add_quantity', 0) }}" placeholder="Enter quantity to add">
                                        @error('add_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Add additional stock to current inventory</small>
                                    </div>

                                    <!-- Second Row: Minimum Stock and Maximum Stock -->
                                    <div class="col-md-6">
                                        <label for="min_stock" class="form-label fw-medium">Minimum Stock *</label>
                                        <input type="number" name="min_stock" id="min_stock" min="0"
                                               class="form-control @error('min_stock') is-invalid @enderror"
                                               value="{{ old('min_stock', $item->min_stock) }}" required>
                                        @error('min_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="max_stock" class="form-label fw-medium">Maximum Stock</label>
                                        <input type="number" name="max_stock" id="max_stock" min="0"
                                               class="form-control @error('max_stock') is-invalid @enderror"
                                               value="{{ old('max_stock', $item->max_stock) }}">
                                        @error('max_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </form>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('items.show', $item->id) }}?type={{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}" class="btn btn-secondary">
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

@endsection
