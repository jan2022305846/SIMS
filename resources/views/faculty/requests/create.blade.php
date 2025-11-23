@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-plus me-2 text-warning"></i>
            New Supply Request
        </h2>
        <a href="{{ route('faculty.requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to My Requests
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Request Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('faculty.requests.store') }}" enctype="multipart/form-data" id="requestForm">
                            @csrf

                            <!-- Request Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Request Type <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="request_type" id="singleRequest" value="single" checked>
                                            <label class="form-check-label" for="singleRequest">
                                                <strong>Single Item Request</strong>
                                                <br><small class="text-muted">Request one item at a time</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="request_type" id="bulkRequest" value="bulk">
                                            <label class="form-check-label" for="bulkRequest">
                                                <strong>Bulk Request</strong>
                                                <br><small class="text-muted">Request multiple items in one request</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Single Item Request Form -->
                            <div id="singleItemForm">
                                <!-- Pre-selected Item (if coming from browse page) -->
                                @if(request('item_id'))
                                    @php
                                        $preselectedItem = \App\Models\Consumable::find(request('item_id')) ?? \App\Models\NonConsumable::find(request('item_id'));
                                    @endphp
                                    @if($preselectedItem)
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Pre-selected Item:</strong> {{ $preselectedItem->name }}
                                            <input type="hidden" name="item_id" value="{{ $preselectedItem->id }}">
                                            <input type="hidden" name="item_type" value="{{ $preselectedItem instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}">
                                        </div>
                                    @endif
                                @endif

                                <!-- Item Selection -->
                                @if(!request('item_id'))
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
                                                        {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }} ({{ $item->quantity }} {{ $item->unit ?? 'pieces' }} available)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Hidden input for item type -->
                                    <input type="hidden" name="item_type" id="item_type" value="">
                                @endif

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
                                           value="{{ old('quantity', 1) }}"
                                           required>
                                    <div class="form-text" id="stock-info">
                                        @if(request('item_id') && $preselectedItem ?? null)
                                            Available: {{ $preselectedItem->quantity }} {{ $preselectedItem->unit ?? 'pieces' }}
                                        @else
                                            Select an item to see available stock
                                        @endif
                                    </div>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Bulk Request Form -->
                            <div id="bulkItemForm" style="display: none;">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-semibold mb-0">
                                            Requested Items <span class="text-danger">*</span>
                                        </label>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i>Add Item
                                        </button>
                                    </div>

                                    <div id="itemsContainer">
                                        <!-- Items will be added here dynamically -->
                                    </div>

                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You can request both consumable and non-consumable items in a single request.
                                    </div>
                                </div>
                            </div>

                            <!-- Department (Hidden - Auto-populated from user profile) -->
                            <input type="hidden" name="department" value="{{ Auth::user()->department ?? 'Faculty Office' }}">

                            <!-- Priority -->
                            <div class="mb-4">
                                <label for="priority" class="form-label fw-semibold">
                                    Priority Level <span class="text-danger">*</span>
                                </label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Select priority...</option>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                                        <i class="fas fa-circle text-success me-1"></i>Low - Can wait
                                    </option>
                                    <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>
                                        <i class="fas fa-circle text-primary me-1"></i>Normal - Standard timeline
                                    </option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                                        <i class="fas fa-circle text-warning me-1"></i>High - Needed soon
                                    </option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                                        <i class="fas fa-circle text-danger me-1"></i>Urgent - Immediate need
                                    </option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Needed Date -->
                            <div class="mb-4">
                                <label for="needed_date" class="form-label fw-semibold">
                                    Needed By Date <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       name="needed_date"
                                       id="needed_date"
                                       class="form-control @error('needed_date') is-invalid @enderror"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('needed_date') }}"
                                       required>
                                <div class="form-text">When do you need this item?</div>
                                @error('needed_date')
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
                                          required>{{ old('purpose') }}</textarea>
                                <div class="form-text">Be specific about how this item will be used</div>
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Attachments -->
                            <div class="mb-4">
                                <label for="attachments" class="form-label fw-semibold">
                                    Attachments (Optional)
                                </label>
                                <input type="file"
                                       name="attachments[]"
                                       id="attachments"
                                       class="form-control @error('attachments.*') is-invalid @enderror"
                                       multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <div class="form-text">
                                    You can attach supporting documents (PDF, images, Word docs). Max 5MB per file.
                                </div>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('faculty.requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning fw-bold">
                                    <i class="fas fa-paper-plane me-1"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-info-circle me-2"></i>What happens next?
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                                        <i class="fas fa-paper-plane text-primary fa-lg"></i>
                                    </div>
                                    <h6 class="mb-1">1. Submit Request</h6>
                                    <small class="text-muted">Your request is submitted and enters the review queue</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                                        <i class="fas fa-user-check text-info fa-lg"></i>
                                    </div>
                                    <h6 class="mb-1">2. Admin Review</h6>
                                    <small class="text-muted">Administrator reviews details and availability</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                                        <i class="fas fa-check-circle text-warning fa-lg"></i>
                                    </div>
                                    <h6 class="mb-1">3. Approval/Processing</h6>
                                    <small class="text-muted">Request approved and items prepared</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <div class="text-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block mb-2">
                                        <i class="fas fa-box text-success fa-lg"></i>
                                    </div>
                                    <h6 class="mb-1">4. Ready for Pickup</h6>
                                    <small class="text-muted">Receive notification when items are ready</small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Processing Time:</strong> Most requests are processed within 1-3 business days. Urgent requests are prioritized.
                        </div>
                        <div class="alert alert-light mt-2">
                            <i class="fas fa-bell me-2"></i>
                            <strong>Stay Updated:</strong> You'll receive email notifications at each step. You can also track your request status in "My Requests".
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
    // Request type switching
    const singleRequestRadio = document.getElementById('singleRequest');
    const bulkRequestRadio = document.getElementById('bulkRequest');
    const singleItemForm = document.getElementById('singleItemForm');
    const bulkItemForm = document.getElementById('bulkItemForm');
    const requestForm = document.getElementById('requestForm');

    // Toggle between single and bulk request forms
    function toggleRequestType() {
        if (singleRequestRadio.checked) {
            singleItemForm.style.display = 'block';
            bulkItemForm.style.display = 'none';
            // Make bulk fields not required
            document.querySelectorAll('#bulkItemForm input[required], #bulkItemForm select[required]').forEach(el => {
                el.required = false;
            });
            // Make single fields required
            document.querySelectorAll('#singleItemForm input[required], #singleItemForm select[required]').forEach(el => {
                el.required = true;
            });
        } else {
            singleItemForm.style.display = 'none';
            bulkItemForm.style.display = 'block';
            // Make single fields not required
            document.querySelectorAll('#singleItemForm input[required], #singleItemForm select[required]').forEach(el => {
                el.required = false;
            });
            // Make bulk fields required (handled by validation)
        }
    }

    singleRequestRadio.addEventListener('change', toggleRequestType);
    bulkRequestRadio.addEventListener('change', toggleRequestType);

    // Bulk request functionality
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');
    let itemIndex = 0;

    // Available items data
    const availableItems = @json($items);

    addItemBtn.addEventListener('click', function() {
        addItemRow();
    });

    function addItemRow(itemData = null) {
        const itemId = itemIndex++;
        const row = document.createElement('div');
        row.className = 'item-row border rounded p-3 mb-3 bg-light';
        row.dataset.index = itemId;

        row.innerHTML = `
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Item <span class="text-danger">*</span></label>
                    <select name="items[${itemId}][item_id]" class="form-select item-select" required>
                        <option value="">Choose an item...</option>
                        ${availableItems.map(item => `
                            <option value="${item.id}"
                                    data-stock="${item.quantity}"
                                    data-unit="${item.unit || 'pieces'}"
                                    data-type="${item.constructor.name === 'Consumable' ? 'consumable' : 'non_consumable'}"
                                    ${itemData && itemData.item_id == item.id ? 'selected' : ''}>
                                ${item.name} (${item.quantity} ${item.unit || 'pieces'} available)
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemId}][quantity]" class="form-control quantity-input"
                           min="1" value="${itemData ? itemData.quantity : 1}" required>
                    <input type="hidden" name="items[${itemId}][item_type]" class="item-type-input">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stock Info</label>
                    <div class="form-text stock-info">Select an item to see stock</div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn d-block">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        itemsContainer.appendChild(row);

        // Add event listeners
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const stockInfo = row.querySelector('.stock-info');
        const itemTypeInput = row.querySelector('.item-type-input');
        const removeBtn = row.querySelector('.remove-item-btn');

        itemSelect.addEventListener('change', function() {
            updateItemInfo(this, stockInfo, itemTypeInput, quantityInput);
        });

        quantityInput.addEventListener('input', function() {
            validateQuantity(this, itemSelect);
        });

        removeBtn.addEventListener('click', function() {
            row.remove();
            updateItemNumbers();
        });

        // Trigger initial update if item is pre-selected
        if (itemSelect.value) {
            updateItemInfo(itemSelect, stockInfo, itemTypeInput, quantityInput);
        }
    }

    function updateItemInfo(selectEl, stockInfoEl, itemTypeInput, quantityInput) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            const unit = selectedOption.getAttribute('data-unit') || 'pieces';
            const itemType = selectedOption.getAttribute('data-type');

            stockInfoEl.textContent = `Available: ${stock} ${unit}`;
            itemTypeInput.value = itemType;
            quantityInput.max = stock;
            quantityInput.value = Math.min(quantityInput.value || 1, stock);
        } else {
            stockInfoEl.textContent = 'Select an item to see stock';
            itemTypeInput.value = '';
            quantityInput.removeAttribute('max');
        }
    }

    function validateQuantity(quantityInput, itemSelect) {
        const maxStock = parseInt(quantityInput.max);
        if (maxStock && parseInt(quantityInput.value) > maxStock) {
            quantityInput.value = maxStock;
        }
    }

    function updateItemNumbers() {
        // Optional: Update any numbering if needed
    }

    // Single item form functionality (existing code)
    const itemSelect = document.getElementById('item_id');
    const quantityInput = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');
    const itemTypeInput = document.getElementById('item_type');

    // Update stock info and item type when item is selected
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            const unit = selectedOption.getAttribute('data-unit') || 'pieces';
            const itemType = selectedOption.getAttribute('data-type');
            stockInfo.textContent = `Available: ${stock} ${unit}`;

            // Set item type
            if (itemTypeInput) {
                itemTypeInput.value = itemType;
            }

            // Set max quantity
            quantityInput.max = stock;
            quantityInput.value = Math.min(quantityInput.value || 1, stock);
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

    // Trigger change event on page load if item is pre-selected
    if (itemSelect.value) {
        itemSelect.dispatchEvent(new Event('change'));
    }

    // Form validation before submit
    requestForm.addEventListener('submit', function(e) {
        if (bulkRequestRadio.checked) {
            const itemRows = itemsContainer.querySelectorAll('.item-row');
            if (itemRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to your bulk request.');
                return false;
            }
        }
    });
});
</script>
@endpush