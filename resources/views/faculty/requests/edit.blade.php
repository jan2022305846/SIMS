@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-edit me-2 text-warning"></i>
            Edit Supply Request
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('faculty.requests.show', $request) }}" class="btn btn-secondary">
                <i class="fas fa-eye me-1"></i>View Request
            </a>
            <a href="{{ route('faculty.requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to My Requests
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Current Request Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Current Request Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Current Item:</strong> {{ $request->item ? $request->item->name : 'Item Not Found' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Current Quantity:</strong> {{ $request->quantity }} {{ $request->item && $request->item->unit ? $request->item->unit : 'pcs' }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Current Purpose:</strong>
                                <div class="mt-1 p-2 bg-light rounded">{{ $request->purpose }}</div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> You can only edit the item requested and the purpose. Other details (priority, needed date) cannot be changed once the request is submitted.
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Request Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('faculty.requests.update', $request) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Item Selection -->
                            <div class="mb-4">
                                <label for="item_id" class="form-label fw-semibold">
                                    Select Item <span class="text-danger">*</span>
                                </label>
                                <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">Choose an item...</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}"
                                                data-stock="{{ $item->quantity }}"
                                                data-unit="{{ $item->unit ?? 'pieces' }}"
                                                data-type="{{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}"
                                                {{ $request->item_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} ({{ $item->quantity }} {{ $item->unit ?? 'pieces' }} available)
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Hidden input for item type -->
                            <input type="hidden" name="item_type" id="item_type" value="{{ $request->item_type }}">

                            <!-- Quantity -->
                            <div class="mb-4">
                                <label for="quantity" class="form-label fw-semibold">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       name="quantity"
                                       id="quantity"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       min="1"
                                       value="{{ old('quantity', $request->quantity) }}"
                                       required>
                                <div class="form-text" id="stock-info">
                                    @if($request->item)
                                        Available: {{ $request->item->quantity }} {{ $request->item->unit ?? 'pieces' }}
                                    @else
                                        Select an item to see available stock
                                    @endif
                                </div>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Purpose -->
                            <div class="mb-4">
                                <label for="purpose" class="form-label fw-semibold">
                                    Purpose/Reason <span class="text-danger">*</span>
                                </label>
                                <textarea name="purpose"
                                          id="purpose"
                                          class="form-control @error('purpose') is-invalid @enderror"
                                          rows="4"
                                          placeholder="Please explain why you need this item and how it will be used..."
                                          required>{{ old('purpose', $request->purpose) }}</textarea>
                                <div class="form-text">Be specific about how this item will be used</div>
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('faculty.requests.show', $request) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning fw-bold">
                                    <i class="fas fa-save me-1"></i>Update Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-info-circle me-2"></i>Important Notes
                        </h6>
                        <div class="alert alert-info">
                            <i class="fas fa-edit me-2"></i>
                            <strong>Editable Fields:</strong> You can change the item requested and the purpose/reason for your request.
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Locked Fields:</strong> Priority level, needed date, and attachments cannot be changed after submission to maintain request integrity.
                        </div>
                        <div class="alert alert-success">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Processing:</strong> Your request will be re-evaluated by the administrator after the update.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item_id');
    const quantityInput = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');
    const itemTypeInput = document.getElementById('item_type');

    // Update stock info and item type when item is selected
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            const unit = selectedOption.getAttribute('data-unit') || 'pcs';
            const itemType = selectedOption.getAttribute('data-type');
            stockInfo.textContent = `Available: ${stock} ${unit}`;

            // Set item type
            if (itemTypeInput) {
                itemTypeInput.value = itemType;
            }

            // Set max quantity
            quantityInput.max = stock;
            // Don't change quantity automatically, let user decide
        } else {
            stockInfo.textContent = 'Select an item to see available stock';
            quantityInput.removeAttribute('max');
            if (itemTypeInput) {
                itemTypeInput.value = '';
            }
        }
    });

    // Validate quantity doesn't exceed stock
    quantityInput.addEventListener('input', function() {
        const maxStock = parseInt(this.max);
        if (maxStock && parseInt(this.value) > maxStock) {
            this.value = maxStock;
        }
    });

    // Trigger change event on page load to set initial values
    if (itemSelect.value) {
        itemSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush