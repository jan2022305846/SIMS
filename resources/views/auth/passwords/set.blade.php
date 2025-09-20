@extends('layouts.app')

@push('styles')
<style>
body {
    margin: 0 !important;
    padding: 0 !important;
}
.h-100 {
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%) !important;
    min-height: 100vh !important;
    width: 100vw !important;
}

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
<div class="container-fluid px-0">
    <div class="row justify-content-center mx-0">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Set Your Password
                    </h4>
                </div>

                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-lock fa-3x text-primary mb-3"></i>
                        <h5 class="text-muted">Please create a new password for your account</h5>
                        <p class="text-muted small">This will activate your SIMS account</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.set') }}" id="set-password-form">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="Enter your email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>New Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       required
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Enter your new password">
                                <button type="button" class="password-toggle" id="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Password must be at least 8 characters long</div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm New Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       placeholder="Confirm your new password">
                                <button type="button" class="password-toggle" id="password-confirm-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Set Password & Activate Account
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        This link will expire in 24 hours for security reasons
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Password confirmation validation
    function validatePasswordConfirmation() {
        const password = passwordInput.value;
        const confirmation = confirmPasswordInput.value;

        if (password && confirmation && password !== confirmation) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    passwordInput.addEventListener('input', validatePasswordConfirmation);
    confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);
});
</script>
@endpush
@endsection