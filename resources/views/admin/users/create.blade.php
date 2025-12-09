@extends('layouts.app')

@push('styles')
<style>
/* Password input wrapper */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrapper .form-control {
    padding-right: 3rem; /* Make room for the toggle button */
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
}

.password-toggle:hover {
    color: #495057;
    background-color: rgba(108, 117, 125, 0.1);
}

.password-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0 text-dark fw-bold">
                            <i class="fas fa-user-plus me-2"></i>
                            Add New User
                        </h1>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Users
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Validation Errors:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('users.store') }}" id="user-form">
                        @csrf

                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div class="col-md-6">
                                <label for="username" class="form-label fw-medium">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}"
                                       placeholder="Use School ID"
                                       required
                                       class="form-control @error('username') is-invalid @enderror">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Role (Hidden - only faculty can be created) -->
                            <input type="hidden" name="role" value="faculty">

                            <!-- Office -->
                            <div class="col-md-6">
                                <label for="office_id" class="form-label fw-medium">
                                    Office
                                </label>
                                <select name="office_id"
                                        id="office_id"
                                        class="form-select @error('office_id') is-invalid @enderror">
                                    <option value="">Select Office...</option>
                                    @foreach(\App\Models\Office::orderBy('name')->get() as $office)
                                        <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" 
                                        id="password" 
                                        name="password" 
                                        required
                                        class="form-control @if($errors->has('password')) is-invalid @endif">
                                    <button type="button" class="password-toggle" id="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @if($errors->has('password'))
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                @endif
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-medium">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" 
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        required
                                        class="form-control @if($errors->has('password')) is-invalid @endif">
                                    <button type="button" class="password-toggle" id="password-confirm-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @if($errors->has('password'))
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" form="user-form" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Create User
                        </button>
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
    // Password toggle for main password field
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const passwordToggleIcon = passwordToggle.querySelector('i');

    passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Password toggle for confirmation field
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const confirmPasswordToggle = document.getElementById('password-confirm-toggle');
    const confirmPasswordToggleIcon = confirmPasswordToggle.querySelector('i');

    confirmPasswordToggle.addEventListener('click', function() {
        const isPassword = confirmPasswordInput.type === 'password';
        confirmPasswordInput.type = isPassword ? 'text' : 'password';
        confirmPasswordToggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Duplicate checking
    const nameInput = document.getElementById('name');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');

    let nameTimeout, usernameTimeout, emailTimeout;

    // Check name duplication
    nameInput.addEventListener('input', function() {
        clearTimeout(nameTimeout);
        const value = this.value.trim();
        if (value.length >= 2) {
            nameTimeout = setTimeout(() => checkDuplicate('name', value, this), 500);
        } else {
            clearFieldError(this);
        }
    });

    // Check username duplication
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        const value = this.value.trim();
        if (value.length >= 3) {
            usernameTimeout = setTimeout(() => checkDuplicate('username', value, this), 500);
        } else {
            clearFieldError(this);
        }
    });

    // Check email duplication
    emailInput.addEventListener('input', function() {
        clearTimeout(emailTimeout);
        const value = this.value.trim();
        if (value.length >= 5 && value.includes('@')) {
            emailTimeout = setTimeout(() => checkDuplicate('email', value, this), 500);
        } else {
            clearFieldError(this);
        }
    });

    // Function to check for duplicates
    async function checkDuplicate(field, value, inputElement) {
        try {
            const response = await fetch(`/admin/api/users/check-duplicate?field=${field}&value=${encodeURIComponent(value)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (data.exists) {
                showFieldError(inputElement, data.message);
            } else {
                clearFieldError(inputElement);
            }
        } catch (error) {
            console.error('Error checking duplicate:', error);
        }
    }

    // Function to show field error
    function showFieldError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');

        // Remove existing error message
        const existingError = inputElement.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }

        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        inputElement.parentNode.appendChild(errorDiv);
    }

    // Function to clear field error
    function clearFieldError(inputElement) {
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');

        const errorMessage = inputElement.parentNode.querySelector('.invalid-feedback');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
});
</script>
@endpush
