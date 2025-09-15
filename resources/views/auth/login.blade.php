@extends('layouts.app')

@push('styles')
<style>
/* Fix the margin/padding issue that was causing white space */
* {
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

html, body {
    height: 100vh !important;
    overflow-x: hidden !important;
}

body.guest {
    height: 100vh !important;
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%) !important;
}

/* Force login card to always be white regardless of dark mode */
.login-card {
    background: white !important;
    color: #212529 !important;
    max-height: 80vh !important;
    overflow-y: auto !important;
    padding: 0 !important;
    margin: 0 !important;
}

.login-card .card-body {
    background: white !important;
    padding: 3rem !important; /* Add back only the padding we need */
}

.login-card .row {
    margin: 0 !important;
}

.login-card .col-md-5,
.login-card .col-md-7 {
    padding: 0 !important;
}

/* Re-add padding only where needed */
.col-md-5 {
    padding: 3rem !important;
}

.login-card .form-label,
.login-card h3,
.login-card p {
    color: #212529 !important;
}

/* Make sure USTP text is white */
.col-md-5 p,
.col-md-5 .text-white {
    color: white !important;
}

.login-card .form-control {
    background: white !important;
    border-color: #dee2e6 !important;
    color: #212529 !important;
}

.login-card .form-control:focus {
    background: white !important;
    border-color: #86b7fe !important;
    color: #212529 !important;
}

/* Form spacing */
.mb-4 {
    margin-bottom: 1.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.text-center {
    margin-bottom: 1.5rem !important;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .login-card {
        width: 95% !important;
        max-width: none !important;
        max-height: 90vh !important;
        margin: 0 !important;
    }
    
    .login-card .row {
        flex-direction: column !important;
    }
    
    .login-card .col-md-5 {
        min-height: 200px !important;
        padding: 2rem 1rem !important;
    }
    
    .login-card .col-md-7 {
        padding: 0 !important;
    }
    
    .login-card .card-body {
        padding: 2rem 1.5rem !important;
    }
    
    /* Mobile branding adjustments */
    .login-card .display-3 {
        font-size: 2.5rem !important;
    }
    
    .login-card .fa-4x {
        font-size: 2.5rem !important;
    }
    
    .login-card .h6 {
        font-size: 0.8rem !important;
        line-height: 1.3 !important;
    }
    
    /* Mobile form adjustments */
    .form-control-lg {
        padding: 0.75rem 1rem !important;
        font-size: 1rem !important;
    }
    
    .btn-lg {
        padding: 0.75rem 1rem !important;
        font-size: 1rem !important;
    }
}

@media (max-width: 480px) {
    .login-card {
        width: 98% !important;
        max-height: 95vh !important;
    }
    
    .login-card .col-md-5 {
        min-height: 180px !important;
        padding: 1.5rem 1rem !important;
    }
    
    .login-card .card-body {
        padding: 1.5rem 1rem !important;
    }
    
    .login-card .display-3 {
        font-size: 2rem !important;
        margin-bottom: 1rem !important;
    }
    
    .login-card .fa-4x {
        font-size: 2rem !important;
    }
}
</style>
@endpush

@section('content')
    <div class="card shadow-lg border-0 overflow-hidden login-card" style="width: 90%; max-width: 1000px;">
        <div class="row g-0 h-100">
                        <!-- Left Side - Branding -->
                        <div class="col-md-5 d-flex flex-column justify-content-center align-items-center p-5" 
                             style="background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);">
                            <div class="text-center text-white">
                                <!-- System Logo/Icon -->
                                <div class="mb-4">
                                    <i class="fas fa-boxes fa-4x text-warning"></i>
                                </div>
                                
                                <!-- Main System Name -->
                                <h1 class="display-3 fw-bold text-warning mb-4" style="letter-spacing: 0.1em;">
                                    SIMS
                                </h1>
                                
                                <!-- Full System Name -->
                                <h2 class="h6 text-warning mb-4 text-uppercase fw-medium" style="letter-spacing: 0.05em;">
                                    Supply Office Inventory<br>
                                    Management System
                                </h2>
                                
                                <hr class="border-warning opacity-50 my-4 w-75 mx-auto">
                                
                                <!-- Institution Name -->
                                <p class="small mb-0 fw-medium text-white">
                                    USTP PANAON SUPPLY OFFICE
                                </p>
                            </div>
                        </div>

                        <!-- Right Side - Login Form -->
                        <div class="col-md-7" style="background: white !important;">
                            <div class="card-body p-5" style="background: white !important;">
                                <div class="text-center mb-4">
                                    <h3 class="h3 fw-bold mb-2" style="color: #212529 !important;">
                                        Welcome Back
                                    </h3>
                                    <p class="mb-0" style="color: #6c757d !important;">Please sign in to your account</p>
                                </div>
                                
                                @if ($errors->any())
                                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>{{ $errors->first() }}</span>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label for="school_id" class="form-label fw-medium" style="color: #212529 !important;">
                                            <i class="fas fa-id-card me-2"></i>School ID
                                        </label>
                                        <input type="text" 
                                               id="school_id" 
                                               name="school_id" 
                                               value="{{ old('school_id') }}" 
                                               required 
                                               autofocus
                                               class="form-control form-control-lg @error('school_id') is-invalid @enderror"
                                               placeholder="Enter your school ID"
                                               style="background: white !important; color: #212529 !important; border-color: #dee2e6 !important;">
                                        @error('school_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label fw-medium" style="color: #212529 !important;">
                                            <i class="fas fa-lock me-2"></i>Password
                                        </label>
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               required
                                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                                               placeholder="Enter your password"
                                               style="background: white !important; color: #212529 !important; border-color: #dee2e6 !important;">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-semibold text-dark py-3 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Sign In to SIMS
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
