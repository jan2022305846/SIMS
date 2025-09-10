@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-9">
                <div class="card shadow-lg border-0 overflow-hidden">
                    <div class="row g-0">
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
                                <p class="small text-white-50 mb-0 fw-medium">
                                    USTP PANAON SUPPLY OFFICE
                                </p>
                            </div>
                        </div>

                        <!-- Right Side - Login Form -->
                        <div class="col-md-7">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <h3 class="h3 fw-bold text-dark mb-2">
                                        Welcome Back
                                    </h3>
                                    <p class="text-muted mb-0">Please sign in to your account</p>
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
                                        <label for="school_id" class="form-label fw-medium text-dark">
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

                                    <div class="mb-4">
                                        <label for="password" class="form-label fw-medium text-dark">
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
        </div>
    </div>
</div>
@endsection
