@extends('layouts.app')

@push('styles')
<style>
/* Production-safe login page styles */
.login-page-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.login-card-container {
    width: 100%;
    max-width: 1000px;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.login-card-row {
    display: flex;
    min-height: 500px;
}

.login-branding-section {
    flex: 1;
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: white;
}

.login-form-section {
    flex: 1;
    background: white;
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Branding content */
.login-branding-section .fa-4x {
    color: #ffc107;
    margin-bottom: 1.5rem;
}

.login-branding-section .display-3 {
    color: #ffc107;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 1rem;
}

.login-branding-section .h6 {
    color: #ffc107;
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 0.05em;
    margin-bottom: 1.5rem;
}

.login-branding-section hr {
    border-color: rgba(255, 193, 7, 0.5);
    width: 75%;
    margin: 1.5rem auto;
}

.login-branding-section .small {
    color: white;
    font-weight: 500;
}

/* Form content */
.login-form-section h3 {
    color: #212529;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.login-form-section .text-muted {
    color: #6c757d;
    margin-bottom: 2rem;
}

.login-form-section .form-group {
    margin-bottom: 1.5rem;
}

.login-form-section .form-label {
    color: #212529;
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: block;
}

.login-form-section .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    font-size: 1rem;
    background-color: white;
    color: #212529;
}

.login-form-section .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.login-form-section .btn {
    background-color: #ffc107;
    border: none;
    color: #212529;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    font-weight: 600;
    width: 100%;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
}

.login-form-section .btn:hover {
    background-color: #e0a800;
    color: #212529;
}

/* Alert styles */
.alert {
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

/* Responsive design */
@media (max-width: 768px) {
    .login-page-wrapper {
        padding: 0.5rem;
    }

    .login-card-row {
        flex-direction: column;
        min-height: auto;
    }

    .login-branding-section,
    .login-form-section {
        flex: none;
        padding: 2rem 1.5rem;
    }

    .login-branding-section {
        min-height: 250px;
    }

    .login-branding-section .fa-4x {
        font-size: 2.5rem;
    }

    .login-branding-section .display-3 {
        font-size: 2rem;
    }

    .login-branding-section .h6 {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .login-page-wrapper {
        padding: 0.25rem;
    }

    .login-card-container {
        border-radius: 0;
        box-shadow: none;
    }

    .login-branding-section,
    .login-form-section {
        padding: 1.5rem 1rem;
    }

    .login-branding-section {
        min-height: 200px;
    }

    .login-branding-section .fa-4x {
        font-size: 2rem;
    }

    .login-branding-section .display-3 {
        font-size: 1.75rem;
    }

    .login-form-section .btn {
        padding: 0.625rem 0.875rem;
        font-size: 0.95rem;
    }
}

/* Ensure no global styles interfere */
.login-page-wrapper *,
.login-page-wrapper *::before,
.login-page-wrapper *::after {
    box-sizing: border-box;
}
</style>
@endpush

@section('content')
<div class="login-page-wrapper">
    <div class="login-card-container">
        <div class="login-card-row">
            <!-- Left Side - Branding -->
            <div class="login-branding-section">
                <!-- System Logo/Icon -->
                <i class="fas fa-boxes fa-4x"></i>

                <!-- Main System Name -->
                <h1 class="display-3 fw-bold mb-4">
                    SIMS
                </h1>

                <!-- Full System Name -->
                <h2 class="h6 mb-4">
                    Supply Office Inventory<br>
                    Management System
                </h2>

                <hr>

                <!-- Institution Name -->
                <p class="small mb-0">
                    USTP PANAON SUPPLY OFFICE
                </p>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-form-section">
                <div class="text-center mb-4">
                    <h3>Welcome Back</h3>
                    <p class="text-muted">Please sign in to your account</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="school_id" class="form-label">
                            <i class="fas fa-id-card me-2"></i>School ID
                        </label>
                        <input type="text"
                               id="school_id"
                               name="school_id"
                               value="{{ old('school_id') }}"
                               required
                               autofocus
                               class="form-control @error('school_id') is-invalid @enderror"
                               placeholder="Enter your school ID">
                        @error('school_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Sign In to SIMS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
