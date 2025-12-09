@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="h3 mb-1 text-dark fw-bold">
                                <i class="fas fa-user-tag me-2 text-primary"></i>
                                Assign Item: {{ $item->name }}
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="fas fa-tags me-1"></i>
                                {{ $item->category->name }}
                            </p>
                        </div>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <a href="{{ route('items.show', $item->id) }}?type={{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Item
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Current Assignment Status -->
                    @if($item->isAssigned())
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Currently Assigned:</strong> This item is currently assigned to {{ $item->currentHolder->name }}
                                ({{ $item->currentHolder->email }}) since {{ $item->updated_at->format('M d, Y') }}.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>
                                <strong>Available for Assignment:</strong> This item is currently available and can be assigned to a user.
                            </div>
                        </div>
                    @endif

                    <!-- Item Details Summary -->
                    <div class="card border-light mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Item Details
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
                                    <p class="h6 mb-0">{{ $item->category->name }} ({{ $item->category->type }})</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Current Location</p>
                                    <p class="h6 mb-0">{{ $item->location ?: 'Not specified' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mb-1">Condition</p>
                                    <p class="h6 mb-0">{{ $item->condition }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Form -->
                    <form action="{{ route('items.assign.store', $item) }}" method="POST">
                        @csrf
                        <div class="card border-light">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Assignment Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- User Selection -->
                                    <div class="col-md-6">
                                        <label for="user_id" class="form-label">
                                            <i class="fas fa-user me-1"></i>
                                            Assign to User <span class="text-danger">*</span>
                                        </label>
                                        <select name="user_id" id="user_id" class="form-select" required>
                                            <option value="">Select a user...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}"
                                                        {{ $item->current_holder_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                    @if($user->office)
                                                        - {{ $user->office->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">Choose the user who will be responsible for this item.</div>
                                    </div>

                                    <!-- Location Update -->
                                    <div class="col-md-6">
                                        <label for="location" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            Update Location
                                        </label>
                                        <div class="position-relative">
                                            <select name="location" id="location" class="form-select">
                                                <option value="">Select a location...</option>
                                                @foreach($offices as $office)
                                                    <option value="{{ $office->name }}"
                                                            {{ $item->location == $office->name ? 'selected' : '' }}>
                                                        {{ $office->name }}
                                                        @if($office->location)
                                                            ({{ $office->location }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                        </div>
                                        <div class="form-text">Update the current location of the item if needed.</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-end mt-4">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('items.show', $item->id) }}?type={{ $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable' }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            {{ $item->isAssigned() ? 'Update Assignment' : 'Assign Item' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Unassign Form (separate from main assignment form) -->
                    @if($item->isAssigned())
                        <div class="mt-3">
                            <form action="{{ route('items.unassign', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to unassign this item from {{ $item->currentHolder->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-user-times me-1"></i>
                                    Unassign Current Holder
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection