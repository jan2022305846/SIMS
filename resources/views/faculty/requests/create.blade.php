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
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Request Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('faculty.requests.store') }}" enctype="multipart/form-data">
                            @csrf

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
                                                    {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }} ({{ $item->quantity }} {{ $item->unit ?? 'pieces' }} available)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
    const itemSelect = document.getElementById('item_id');
    const quantityInput = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');

    // Update stock info when item is selected
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            const unit = selectedOption.getAttribute('data-unit') || 'pcs';
            stockInfo.textContent = `Available: ${stock} ${unit}`;

            // Set max quantity
            quantityInput.max = stock;
            quantityInput.value = Math.min(quantityInput.value || 1, stock);
        } else {
            stockInfo.textContent = 'Select an item to see available stock';
            quantityInput.removeAttribute('max');
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
});
</script>
@endpush