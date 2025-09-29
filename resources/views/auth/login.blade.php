@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="login-page-wrapper">
    <div class="login-card-container">
        <div class="login-card-row">
            <!-- Left Side - Branding -->
            <div class="login-branding-section">
                <!-- System Logo/Icon -->
                <img src="{{ asset('logos/USTP Logo against Dark Background.png') }}"
                     alt="USTP Logo"
                     class="ustp-logo mb-4"
                     style="max-width: 150px; height: auto; filter: brightness(1.1);">

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

                {{-- Error messages are shown below each field --}}
                <div id="login-error" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="login-error-message"></span>
                </div>

                <form id="login-form" method="POST" action="{{ route('login') }}">
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
                        <div id="school_id-error" class="invalid-feedback d-block" style="display: none;"></div>
                        @error('school_id')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Enter your password">
                            <button type="button" class="password-toggle" id="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-error" class="invalid-feedback d-block" style="display: none;"></div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <a href="#" class="forgot-password-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            <i class="fas fa-question-circle me-1"></i>Forgot Password?
                        </a>
                    </div>

                    <button type="submit" id="login-submit" class="btn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true" style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 1065 !important;">
    <div class="modal-dialog modal-dialog-centered" style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; z-index: 1070 !important; margin: 0 !important; pointer-events: auto !important; width: 90% !important; max-width: 500px !important; min-height: 300px !important;">
        <div class="modal-content" style="border: none !important; border-radius: 20px !important; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important; pointer-events: auto !important; position: relative !important; z-index: 1071 !important; background-color: white !important; min-height: 300px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Reset Your Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="forgot-password-form">
                    <p class="text-muted mb-4">
                        Enter your school ID below and we'll send you a link to reset your password.
                    </p>

                    <form id="forgot-password-form-element">
                        @csrf
                        <div class="form-group">
                            <label for="forgot_school_id" class="form-label">
                                <i class="fas fa-id-card me-2"></i>School ID
                            </label>
                            <input type="text"
                                   id="forgot_school_id"
                                   name="school_id"
                                   required
                                   class="form-control"
                                   placeholder="Enter your school ID">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn" id="forgot-password-submit">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Reset Link
                            </button>
                        </div>
                    </form>
                </div>

                <div id="forgot-password-success" style="display: none;">
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success mb-3">Reset Link Sent!</h5>
                        <p class="text-muted">
                            We've sent a password reset link to your email address. Please check your inbox and follow the instructions to reset your password.
                        </p>
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>

                <div id="forgot-password-error" style="display: none;">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="forgot-password-error-message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if modal elements exist
    const modalElement = document.getElementById('forgotPasswordModal');
    const forgotPasswordLink = document.querySelector('[data-bs-target="#forgotPasswordModal"]');

    if (!modalElement) {
        return;
    }

    // Initialize Bootstrap modal
    const forgotPasswordModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });

    // Ensure modal is properly positioned when shown
    modalElement.addEventListener('shown.bs.modal', function() {
        // Move modal to body for proper positioning
        if (modalElement.parentNode !== document.body) {
            document.body.appendChild(modalElement);
        }

        // Create backdrop if it doesn't exist
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '1055';
            document.body.appendChild(backdrop);
        }

        // Apply proper modal positioning
        modalElement.style.position = 'fixed';
        modalElement.style.top = '0';
        modalElement.style.left = '0';
        modalElement.style.width = '100%';
        modalElement.style.height = '100%';
        modalElement.style.zIndex = '1055';
        modalElement.style.display = 'block';
        modalElement.style.overflow = 'hidden';
        modalElement.style.outline = '0';

        const modalDialog = modalElement.querySelector('.modal-dialog');
        const modalContent = modalElement.querySelector('.modal-content');

        if (modalDialog) {
            modalDialog.style.position = 'relative';
            modalDialog.style.width = 'auto';
            modalDialog.style.maxWidth = '500px';
            modalDialog.style.margin = '1.75rem auto';
            modalDialog.style.pointerEvents = 'none';
            modalDialog.style.zIndex = '1065';
        }

        if (modalContent) {
            modalContent.style.position = 'relative';
            modalContent.style.display = 'flex';
            modalContent.style.flexDirection = 'column';
            modalContent.style.width = '100%';
            modalContent.style.pointerEvents = 'auto';
            modalContent.style.backgroundColor = 'white';
            modalContent.style.backgroundClip = 'padding-box';
            modalContent.style.border = '1px solid rgba(0, 0, 0, 0.2)';
            modalContent.style.borderRadius = '0.3rem';
            modalContent.style.outline = '0';
            modalContent.style.boxShadow = '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075), 0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            modalContent.style.zIndex = '1070';
        }
    });

    // Password toggle functionality
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const toggleIcon = passwordToggle.querySelector('i');

    passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Login form AJAX functionality
    const loginForm = document.getElementById('login-form');
    const loginSubmit = document.getElementById('login-submit');
    const loginError = document.getElementById('login-error');
    const loginErrorMessage = document.getElementById('login-error-message');
    const schoolIdError = document.getElementById('school_id-error');
    const passwordError = document.getElementById('password-error');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const schoolId = document.getElementById('school_id').value.trim();
        const password = document.getElementById('password').value;

        if (!schoolId || !password) {
            showLoginError('Please fill in all fields.');
            return;
        }

        // Show loading state
        loginSubmit.disabled = true;
        loginSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in...';

        // Hide previous errors
        loginError.style.display = 'none';
        schoolIdError.style.display = 'none';
        passwordError.style.display = 'none';

        // Prepare form data
        const formData = new FormData(loginForm);

        // Send AJAX request
        fetch('{{ route("login") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                // Successful login - redirect
                window.location.href = response.url;
                return;
            }

            return response.json().then(data => {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        })
        .then(data => {
            // If we get here, login was successful
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '{{ route("dashboard") }}';
            }
        })
        .catch(error => {
            // Handle different types of errors
            if (error.errors) {
                // Validation errors
                if (error.errors.school_id) {
                    schoolIdError.textContent = error.errors.school_id[0];
                    schoolIdError.style.display = 'block';
                }
                if (error.errors.password) {
                    passwordError.textContent = error.errors.password[0];
                    passwordError.style.display = 'block';
                }
            } else if (error.message) {
                // General error message
                showLoginError(error.message);
            } else {
                // Network or other error
                showLoginError('Network error. Please check your connection and try again.');
            }
        })
        .finally(() => {
            // Reset button state
            loginSubmit.disabled = false;
            loginSubmit.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign in';
        });
    });

    function showLoginError(message) {
        loginErrorMessage.textContent = message;
        loginError.style.display = 'block';
    }

    // Forgot password modal functionality
    const forgotPasswordForm = document.getElementById('forgot-password-form-element');
    const forgotPasswordSubmit = document.getElementById('forgot-password-submit');
    const forgotPasswordFormDiv = document.getElementById('forgot-password-form');
    const forgotPasswordSuccess = document.getElementById('forgot-password-success');
    const forgotPasswordError = document.getElementById('forgot-password-error');
    const forgotPasswordErrorMessage = document.getElementById('forgot-password-error-message');

    forgotPasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const schoolId = document.getElementById('forgot_school_id').value.trim();

        if (!schoolId) {
            showForgotPasswordError('Please enter your school ID.');
            return;
        }

        // Show loading state
        forgotPasswordSubmit.disabled = true;
        forgotPasswordSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';

        // Hide previous error
        forgotPasswordError.style.display = 'none';

        // Send AJAX request
        fetch('{{ route("password.forgot.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                school_id: schoolId
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'An error occurred. Please try again.');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                forgotPasswordFormDiv.style.display = 'none';
                forgotPasswordSuccess.style.display = 'block';
            } else {
                showForgotPasswordError(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('Forgot password error:', error);
            showForgotPasswordError(error.message || 'Network error. Please check your connection and try again.');
        })
        .finally(() => {
            // Reset button state
            forgotPasswordSubmit.disabled = false;
            forgotPasswordSubmit.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Reset Link';
        });
    });

    function showForgotPasswordError(message) {
        forgotPasswordErrorMessage.textContent = message;
        forgotPasswordError.style.display = 'block';
    }

    // Reset modal when closed
    modalElement.addEventListener('hidden.bs.modal', function() {
        forgotPasswordFormDiv.style.display = 'block';
        forgotPasswordSuccess.style.display = 'none';
        forgotPasswordError.style.display = 'none';
        document.getElementById('forgot_school_id').value = '';
        forgotPasswordSubmit.disabled = false;
        forgotPasswordSubmit.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Reset Link';

        // Move modal back to original position if needed
        const originalContainer = document.querySelector('.guest-main') || document.body;
        if (modalElement.parentNode === document.body && originalContainer !== document.body) {
            originalContainer.appendChild(modalElement);
        }
    });
});
</script>
@endsection
