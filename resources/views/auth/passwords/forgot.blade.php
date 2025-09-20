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
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center mx-0">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Forgot Your Password?
                    </h4>
                </div>

                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                        <h5 class="text-muted">No worries! We'll help you reset it.</h5>
                        <p class="text-muted small">Enter your email address and we'll send you a link to reset your password</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.forgot.send') }}">
                        @csrf

                        <div class="mb-4">
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
                            <div class="form-text">We'll send a password reset link to this email</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Reset Link
                            </button>

                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Remember your password? <a href="{{ route('login') }}">Sign in here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection