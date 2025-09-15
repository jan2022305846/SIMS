@extends('layouts.app')

@push('styles')
<style>
/* Production-safe CSS reset and layout */
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    padding: 1rem;
}

/* Override any global body styles that might interfere */
body.guest {
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%) !important;
    margin: 0;
    padding: 0;
}

/* Force login card to always be white regardless of dark mode */
.login-card {
    background: white !important;
    color: #212529 !important;
    width: 100%;
    max-width: 1000px;
    max-height: 90vh;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.login-card .card-body {
    background: white !important;
    padding: 3rem;
}

.login-card .row {
    margin: 0;
    height: 100%;
}

.login-card .col-md-5,
.login-card .col-md-7 {
    padding: 0;
}

/* Left side branding styles */
.login-branding {
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    min-height: 500px;
}

.login-card .form-label,
.login-card h3,
.login-card p {
    color: #212529 !important;
}

/* Make sure USTP text is white */
.login-branding .text-white,
.login-branding p {
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
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Form spacing */
.form-group {
    margin-bottom: 1.5rem;
}

.text-center {
    margin-bottom: 1.5rem;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .login-container {
        padding: 0.5rem;
    }
    
    .login-card {
        max-height: 95vh;
        overflow-y: auto;
    }
    
    .login-card .row {
        flex-direction: column;
    }
    
    .login-branding {
        min-height: 200px;
        padding: 2rem 1rem;
    }
    
    .login-card .card-body {
        padding: 2rem 1.5rem;
    }
    
    /* Mobile branding adjustments */
    .login-card .display-3 {
        font-size: 2.5rem;
    }
    
    .login-card .fa-4x {
        font-size: 2.5rem;
    }
    
    .login-card .h6 {
        font-size: 0.8rem;
        line-height: 1.3;
    }
    
    /* Mobile form adjustments */
    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 0.25rem;
    }
    
    .login-card {
        max-height: 98vh;
    }
    
    .login-branding {
        min-height: 180px;
        padding: 1.5rem 1rem;
    }
    
    .login-card .card-body {
        padding: 1.5rem 1rem;
    }
    
    .login-card .display-3 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .login-card .fa-4x {
        font-size: 2rem;
    }
}
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="card shadow-lg border-0 login-card">
        <div class="row g-0 h-100">
            <!-- Left Side - Branding -->
            <div class="col-md-5 login-branding">
                <div class="text-white">
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
            <div class="col-md-7" style="background: white;">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="h3 fw-bold mb-2">
                            Welcome Back
                        </h3>
                        <p class="mb-0 text-muted">Please sign in to your account</p>
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="school_id" class="form-label fw-medium">
                                <i class="fas fa-id-card me-2"></i>School ID
                            </label>
                            <input type="text" 
                                   id="school_id" 
                                   name="school_id" 
                                   value="{{ old('school_id') }}" 
                                   required 
                                   autofocus
                                   class="form-control form-control-lg @error('school_id') is-invalid @enderror"
                                   placeholder="Enter your school ID">
                            @error('school_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label fw-medium">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   placeholder="Enter your password">
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
</div>
@endsection
