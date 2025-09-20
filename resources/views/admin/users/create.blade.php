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
                                       required
                                       class="form-control @error('username') is-invalid @enderror">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- School ID -->
                            <div class="col-md-6">
                                <label for="school_id" class="form-label fw-medium">
                                    School ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="school_id" 
                                       name="school_id" 
                                       value="{{ old('school_id') }}" 
                                       required
                                       class="form-control @error('school_id') is-invalid @enderror">
                                @error('school_id')
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

                            <!-- Role -->
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-medium">
                                    Role <span class="text-danger">*</span>
                                </label>
                                <select id="role" 
                                        name="role" 
                                        required
                                        class="form-select @error('role') is-invalid @enderror">
                                    <option value="">Select Role</option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div class="col-md-6">
                                <label for="department" class="form-label fw-medium">
                                    Department
                                </label>
                                <select name="department"
                                        id="department"
                                        class="form-select @error('department') is-invalid @enderror">
                                    <option value="">Select Department...</option>
                                    <option value="Campus Director" {{ old('department') === 'Campus Director' ? 'selected' : '' }}>Campus Director</option>
                                    <option value="Admin Head Office" {{ old('department') === 'Admin Head Office' ? 'selected' : '' }}>Admin Head Office</option>
                                    <option value="Office of the Academic Head" {{ old('department') === 'Office of the Academic Head' ? 'selected' : '' }}>Office of the Academic Head</option>
                                    <option value="Student Affairs Office" {{ old('department') === 'Student Affairs Office' ? 'selected' : '' }}>Student Affairs Office</option>
                                    <option value="HRMO" {{ old('department') === 'HRMO' ? 'selected' : '' }}>HRMO</option>
                                    <option value="CiTL" {{ old('department') === 'CiTL' ? 'selected' : '' }}>CiTL</option>
                                    <option value="Arts and Culture Office" {{ old('department') === 'Arts and Culture Office' ? 'selected' : '' }}>Arts and Culture Office</option>
                                    <option value="Sports Office" {{ old('department') === 'Sports Office' ? 'selected' : '' }}>Sports Office</option>
                                    <option value="CET Office" {{ old('department') === 'CET Office' ? 'selected' : '' }}>CET Office</option>
                                    <option value="Admission Office" {{ old('department') === 'Admission Office' ? 'selected' : '' }}>Admission Office</option>
                                    <option value="Budget Office" {{ old('department') === 'Budget Office' ? 'selected' : '' }}>Budget Office</option>
                                    <option value="Accounting Office" {{ old('department') === 'Accounting Office' ? 'selected' : '' }}>Accounting Office</option>
                                    <option value="Registrars Office" {{ old('department') === 'Registrars Office' ? 'selected' : '' }}>Registrars Office</option>
                                    <option value="Quaa Office" {{ old('department') === 'Quaa Office' ? 'selected' : '' }}>Quaa Office</option>
                                    <option value="Assessment Office" {{ old('department') === 'Assessment Office' ? 'selected' : '' }}>Assessment Office</option>
                                    <option value="Research and Extension Office" {{ old('department') === 'Research and Extension Office' ? 'selected' : '' }}>Research and Extension Office</option>
                                    <option value="NSTP Office" {{ old('department') === 'NSTP Office' ? 'selected' : '' }}>NSTP Office</option>
                                    <option value="School Library" {{ old('department') === 'School Library' ? 'selected' : '' }}>School Library</option>
                                    <option value="ICT Library" {{ old('department') === 'ICT Library' ? 'selected' : '' }}>ICT Library</option>
                                    <option value="Clinic" {{ old('department') === 'Clinic' ? 'selected' : '' }}>Clinic</option>
                                    <option value="IT Department Head" {{ old('department') === 'IT Department Head' ? 'selected' : '' }}>IT Department Head</option>
                                    <option value="Education Department Head" {{ old('department') === 'Education Department Head' ? 'selected' : '' }}>Education Department Head</option>
                                    <option value="MB Department Head" {{ old('department') === 'MB Department Head' ? 'selected' : '' }}>MB Department Head</option>
                                    <option value="Faculty Office" {{ old('department') === 'Faculty Office' ? 'selected' : '' }}>Faculty Office</option>
                                </select>
                                @error('department')
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
                                           class="form-control @error('password') is-invalid @enderror">
                                    <button type="button" class="password-toggle" id="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                           class="form-control">
                                    <button type="button" class="password-toggle" id="password-confirm-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
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
});
</script>
@endpush
