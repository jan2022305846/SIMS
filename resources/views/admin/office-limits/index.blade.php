@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-chart-bar me-2 text-warning"></i>
                        Office Item Limits Management
                    </h2>
                    <a href="{{ route('requests.manage') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Requests
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Office Filter -->
                        <div class="mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-filter me-2"></i>Select Office
                                    </h5>
                                </div>
                                <div class="col-md-9">
                                    <form method="GET" action="{{ route('office.limits') }}" id="officeFilterForm">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-5">
                                                <select class="form-select" id="office_id" name="office_id">
                                                    @foreach($offices as $office)
                                                        <option value="{{ $office->id }}" {{ $selectedOffice && $selectedOffice->id == $office->id ? 'selected' : '' }}>
                                                            {{ $office->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-search me-1"></i>Load Limits
                                                </button>
                                            </div>
                                            <div class="col-md-3">
                                                <a href="{{ route('office.limits') }}" class="btn btn-outline-secondary w-100">
                                                    <i class="fas fa-undo me-1"></i>Reset
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if($selectedOffice)
                        <div class="mb-3">
                            <h5 class="text-primary">
                                <i class="fas fa-building me-2"></i>{{ $selectedOffice->name }} - Consumable Item Limits
                            </h5>
                            <p class="text-muted small">Set monthly allocation limits for consumable items. Leave blank or set to 0 for unlimited.</p>
                        </div>

                        <form method="POST" action="{{ route('office.limits.update') }}">
                            @csrf
                            <input type="hidden" name="office_id" value="{{ $selectedOffice->id }}">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 60px;">#</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col" class="text-center">Unit</th>
                                            <th scope="col" class="text-center">Current Stock</th>
                                            <th scope="col" class="text-center">Monthly Limit</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consumables as $index => $consumable)
                                            <tr>
                                                <td class="text-center fw-semibold">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $consumable->name }}</div>
                                                    @if($consumable->description)
                                                        <small class="text-muted">{{ Str::limit($consumable->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ $consumable->unit }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-semibold {{ $consumable->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($consumable->quantity) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" 
                                                           name="limits[{{ $consumable->id }}]" 
                                                           value="{{ $limits->get($consumable->id)->max_quantity ?? '' }}" 
                                                           class="form-control form-control-sm text-center" 
                                                           min="0" 
                                                           placeholder="Unlimited"
                                                           style="width: 100px; margin: 0 auto;">
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $limit = $limits->get($consumable->id);
                                                        $hasLimit = $limit && $limit->max_quantity > 0;
                                                    @endphp
                                                    @if($hasLimit)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Limited
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-infinity me-1"></i>Unlimited
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    <small>Showing {{ $consumables->count() }} consumable items for {{ $selectedOffice->name }}</small>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Limits
                                    </button>
                                    <a href="{{ route('office.limits') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-times me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle p-4 mb-3 d-inline-block">
                                <i class="fas fa-building fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Office Selected</h5>
                            <p class="text-muted">Please select an office to manage item limits.</p>
                        </div>
                        @endif
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
    // Auto-submit office filter on change
    const officeSelect = document.getElementById('office_id');
    const form = document.getElementById('officeFilterForm');

    if (officeSelect && form) {
        officeSelect.addEventListener('change', function() {
            form.submit();
        });
    }

    // Add input validation for limit fields
    const limitInputs = document.querySelectorAll('input[type="number"][name^="limits"]');
    limitInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
});
</script>
@endpush