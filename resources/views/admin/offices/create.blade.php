@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0 text-dark fw-bold">
                            <i class="fas fa-plus me-2 text-primary"></i>
                            Create New Office
                        </h1>
                        <a href="{{ route('admin.offices.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Offices
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.offices.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Office Name -->
                            <div class="col-md-8">
                                <label for="name" class="form-label">
                                    <i class="fas fa-building me-1"></i>
                                    Office Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                <div class="form-text">Enter the full name of the office or department.</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Office Code -->
                            <div class="col-md-4">
                                <label for="code" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>
                                    Office Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" required style="text-transform: uppercase;">
                                <div class="form-text">Short code (e.g., ICT, BSIT, SUPPLY).</div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="col-12">
                                <label for="location" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Location
                                </label>
                                <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location') }}" placeholder="e.g., Main Building, Room 201">
                                <div class="form-text">Physical location or address of the office.</div>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Office Head -->
                            <div class="col-12">
                                <label for="office_head_id" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>
                                    Office Head
                                </label>
                                <select name="office_head_id" id="office_head_id" class="form-select @error('office_head_id') is-invalid @enderror">
                                    <option value="">Select office head (optional)...</option>
                                    @foreach($availableHeads as $user)
                                        <option value="{{ $user->id }}" {{ old('office_head_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Assign an office head from available administrators or office heads.</div>
                                @error('office_head_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    Description
                                </label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3" placeholder="Brief description of the office and its functions...">{{ old('description') }}</textarea>
                                <div class="form-text">Optional description of the office's purpose and responsibilities.</div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.offices.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Create Office
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-uppercase office code
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endsection