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
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <div>
                        <h1 class="h2 mb-1 text-dark fw-bold">Edit User</h1>
                        <p class="text-muted mb-0">Update user information and permissions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            View User
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Users
                        </a>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-edit me-2"></i>
                                    Edit User Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('users.update', $user) }}" id="user-form">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3">
                                        <!-- Name -->
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   id="name"
                                                   name="name"
                                                   value="{{ old('name', $user->name) }}"
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Username -->
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   class="form-control @error('username') is-invalid @enderror"
                                                   id="username"
                                                   name="username"
                                                   value="{{ old('username', $user->username) }}"
                                                   required>
                                            @error('username')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- School ID -->
                                        <div class="col-md-6">
                                            <label for="school_id" class="form-label">School ID <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   class="form-control @error('school_id') is-invalid @enderror"
                                                   id="school_id"
                                                   name="school_id"
                                                   value="{{ old('school_id', $user->school_id) }}"
                                                   required>
                                            @error('school_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   id="email"
                                                   name="email"
                                                   value="{{ old('email', $user->email) }}"
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Role -->
                                        <div class="col-md-6">
                                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                            <select class="form-select @error('role') is-invalid @enderror"
                                                    id="role"
                                                    name="role"
                                                    required>
                                                <option value="">Select Role</option>
                                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="faculty" {{ old('role', $user->role) === 'faculty' ? 'selected' : '' }}>Faculty</option>
                                            </select>
                                            @error('role')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Department -->
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <select name="department"
                                                    id="department"
                                                    class="form-select @error('department') is-invalid @enderror">
                                                <option value="">Select Department...</option>
                                                <option value="Campus Director" {{ old('department', $user->department) === 'Campus Director' ? 'selected' : '' }}>Campus Director</option>
                                                <option value="Admin Head Office" {{ old('department', $user->department) === 'Admin Head Office' ? 'selected' : '' }}>Admin Head Office</option>
                                                <option value="Office of the Academic Head" {{ old('department', $user->department) === 'Office of the Academic Head' ? 'selected' : '' }}>Office of the Academic Head</option>
                                                <option value="Student Affairs Office" {{ old('department', $user->department) === 'Student Affairs Office' ? 'selected' : '' }}>Student Affairs Office</option>
                                                <option value="HRMO" {{ old('department', $user->department) === 'HRMO' ? 'selected' : '' }}>HRMO</option>
                                                <option value="CiTL" {{ old('department', $user->department) === 'CiTL' ? 'selected' : '' }}>CiTL</option>
                                                <option value="Arts and Culture Office" {{ old('department', $user->department) === 'Arts and Culture Office' ? 'selected' : '' }}>Arts and Culture Office</option>
                                                <option value="Sports Office" {{ old('department', $user->department) === 'Sports Office' ? 'selected' : '' }}>Sports Office</option>
                                                <option value="CET Office" {{ old('department', $user->department) === 'CET Office' ? 'selected' : '' }}>CET Office</option>
                                                <option value="Admission Office" {{ old('department', $user->department) === 'Admission Office' ? 'selected' : '' }}>Admission Office</option>
                                                <option value="Budget Office" {{ old('department', $user->department) === 'Budget Office' ? 'selected' : '' }}>Budget Office</option>
                                                <option value="Accounting Office" {{ old('department', $user->department) === 'Accounting Office' ? 'selected' : '' }}>Accounting Office</option>
                                                <option value="Registrars Office" {{ old('department', $user->department) === 'Registrars Office' ? 'selected' : '' }}>Registrars Office</option>
                                                <option value="Quaa Office" {{ old('department', $user->department) === 'Quaa Office' ? 'selected' : '' }}>Quaa Office</option>
                                                <option value="Assessment Office" {{ old('department', $user->department) === 'Assessment Office' ? 'selected' : '' }}>Assessment Office</option>
                                                <option value="Research and Extension Office" {{ old('department', $user->department) === 'Research and Extension Office' ? 'selected' : '' }}>Research and Extension Office</option>
                                                <option value="NSTP Office" {{ old('department', $user->department) === 'NSTP Office' ? 'selected' : '' }}>NSTP Office</option>
                                                <option value="School Library" {{ old('department', $user->department) === 'School Library' ? 'selected' : '' }}>School Library</option>
                                                <option value="ICT Library" {{ old('department', $user->department) === 'ICT Library' ? 'selected' : '' }}>ICT Library</option>
                                                <option value="Clinic" {{ old('department', $user->department) === 'Clinic' ? 'selected' : '' }}>Clinic</option>
                                                <option value="IT Department Head" {{ old('department', $user->department) === 'IT Department Head' ? 'selected' : '' }}>IT Department Head</option>
                                                <option value="Education Department Head" {{ old('department', $user->department) === 'Education Department Head' ? 'selected' : '' }}>Education Department Head</option>
                                                <option value="MB Department Head" {{ old('department', $user->department) === 'MB Department Head' ? 'selected' : '' }}>MB Department Head</option>
                                                <option value="Faculty Office" {{ old('department', $user->department) === 'Faculty Office' ? 'selected' : '' }}>Faculty Office</option>
                                            </select>
                                            @error('department')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div class="col-12">
                                            <label for="password" class="form-label">New Password</label>
                                            <div class="password-input-wrapper">
                                                <input type="password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       id="password"
                                                       name="password">
                                                <button type="button" class="password-toggle" id="password-toggle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Leave blank to keep current password</div>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Password Confirmation -->
                                        <div class="col-12">
                                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                            <div class="password-input-wrapper">
                                                <input type="password"
                                                       class="form-control"
                                                       id="password_confirmation"
                                                       name="password_confirmation">
                                                <button type="button" class="password-toggle" id="password-confirm-toggle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            Update User
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    passwordInput.addEventListener('input', validatePasswordConfirmation);
    confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);

    // Form validation
    const form = document.getElementById('user-form');
    form.addEventListener('submit', function(e) {
        if (passwordInput.value && passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    });
});
</script>
@endsection