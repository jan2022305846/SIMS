@extends('layouts.app')

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
                                <input type="text" 
                                       id="department" 
                                       name="department" 
                                       value="{{ old('department') }}"
                                       class="form-control @error('department') is-invalid @enderror">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-medium">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       class="form-control">
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
