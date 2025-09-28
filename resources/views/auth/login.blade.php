@extends('layouts.app')

@push('styles')
<style>
/* Guest main container */
.guest-main {
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    width: 100vw;
    height: 100vh;
    margin: 0;
    padding: 20px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
}

/* Production-safe login page styles */
.login-page-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1851 0%, #0d4a77 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-card-container {
    width: 100%;
    max-width: 1200px;
    background: whi        .finally(() => {
            // Small delay to ensure visual feedback
            setTimeout(() => {
                // Reset button state
                loginSubmit.disabled = false;
                loginSubmit.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign in';
            }, 100);
        });   border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    position: relative;
}

.login-card-row {
    display: flex;
    min-height: 550px;
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
    position: relative;
    overflow: hidden;
}

.login-branding-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
    50% { transform: translate(-50%, -50%) rotate(180deg); }
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
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.login-branding-section .display-3 {
    color: #ffc107;
    font-weight: 700;
    letter-spacing: 0.1em;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    font-size: 2.5rem;
}

.login-branding-section .h6 {
    color: #ffc107;
    text-transform: uppercase;
    font-weight: 500;
    letter-spacing: 0.05em;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.login-branding-section hr {
    border-color: rgba(255, 255, 255, 0.3);
    width: 75%;
    margin: 1.5rem auto;
}

.login-branding-section .small {
    color: #ffffff;
    font-weight: 500;
    opacity: 0.8;
}

/* Form content */
.login-form-section h3 {
    color: #2d3748;
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-size: 1.75rem;
}

.login-form-section .text-muted {
    color: #718096;
    margin-bottom: 2rem;
    font-size: 0.95rem;
}

.login-form-section .form-group {
    margin-bottom: 1.5rem;
}

.login-form-section .form-label {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.9rem;
}

.login-form-section .form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    background-color: #f8fafc;
    color: #2d3748;
    transition: all 0.3s ease;
}

.login-form-section .form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1);
    outline: none;
    background-color: white;
}

.login-form-section .form-control:hover {
    border-color: #cbd5e0;
}

.login-form-section .btn {
    background-color: #ffc107;
    border: none;
    color: #212529;
    padding: 0.875rem 1rem;
    border-radius: 12px;
    font-weight: 600;
    width: 100%;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.login-form-section .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.login-form-section .btn:hover::before {
    left: 100%;
}

.login-form-section .btn:hover {
    background-color: #e0a800;
    color: #212529;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
}

.login-form-section .btn:disabled {
    background-color: #ffc107 !important;
    color: #212529 !important;
    opacity: 0.8 !important;
    cursor: not-allowed !important;
    transform: none !important;
    box-shadow: none !important;
    visibility: visible !important;
    display: block !important;
}

/* Password input wrapper */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrapper .form-control {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #718096;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
}

.password-toggle:hover {
    color: #4a5568;
    background-color: rgba(255, 193, 7, 0.1);
}

.password-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

/* Forgot password link */
.forgot-password-link {
    color: #ffc107;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.2s ease;
    cursor: pointer;
}

.forgot-password-link:hover {
    color: #e0a800;
    text-decoration: underline;
}

/* Alert styles */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: none;
    font-size: 0.9rem;
}

.alert-danger {
    background-color: #fed7d7;
    color: #c53030;
}

.alert-success {
    background-color: #c6f6d5;
    color: #276749;
}

.modal.show {
    display: block !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 1065 !important;
}

.modal-dialog {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 1070 !important;
    margin: 0 !important;
    pointer-events: auto !important;
    width: 90% !important;
    max-width: 500px !important;
    min-height: 300px !important;
}

.modal-content {
    border: none !important;
    border-radius: 20px !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
    pointer-events: auto !important;
    position: relative !important;
    z-index: 1071 !important;
    background-color: white !important;
    min-height: 300px !important;
}

.modal-header {
    border-bottom: 1px solid #e2e8f0;
    padding: 2rem 2rem 1rem;
}

.modal-header .btn-close {
    margin: 0;
}

.modal-title {
    color: #2d3748;
    font-weight: 700;
    font-size: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    border-top: 1px solid #e2e8f0;
    padding: 1rem 2rem 2rem;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
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
        min-height: 300px;
    }

    .login-branding-section .fa-4x {
        font-size: 3rem;
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
        border-radius: 15px;
        box-shadow: none;
    }

    .login-branding-section,
    .login-form-section {
        padding: 1.5rem 1rem;
    }

    .login-branding-section {
        min-height: 250px;
    }

    .login-branding-section .fa-4x {
        font-size: 2.5rem;
    }

    .login-branding-section .display-3 {
        font-size: 1.75rem;
    }

    .login-form-section .btn {
        padding: 0.75rem 0.875rem;
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
