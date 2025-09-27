@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <!-- Faculty Dashboard JavaScript -->
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <!-- Welcome Section -->
                        <div class="col-12">
                            <div class="text-center">
                                <h2 class="fw-bold mb-3">
                                    <i class="fas fa-tachometer-alt me-2" style="color: var(--accent-primary);"></i>
                                    Welcome back, {{ Auth::user()->name }}!
                                </h2>
                                <div class="mb-3">
                                    <span class="badge bg-warning fs-6 px-3 py-2">
                                        <i class="fas fa-user-graduate me-1"></i>
                                        Faculty Dashboard
                                    </span>
                                </div>
                                <p class="text-muted mb-0">
                                    Browse items, create requests, and track your submissions.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <!-- My Requests Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-file-alt fa-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">My Requests</h6>
                                    <h2 class="stats-value">{{ number_format($myRequests ?? 0) }}</h2>
                                    <small class="text-info">
                                        <i class="fas fa-list me-1"></i>Total submitted
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-clock fa-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending</h6>
                                    <h2 class="stats-value">{{ number_format($myPendingRequests ?? 0) }}</h2>
                                    <small class="text-warning">
                                        <i class="fas fa-hourglass-half me-1"></i>Under review
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approved Requests Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-check-circle fa-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Approved</h6>
                                    <h2 class="stats-value">{{ number_format($myApprovedRequests ?? 0) }}</h2>
                                    <small class="text-success">
                                        <i class="fas fa-thumbs-up me-1"></i>Ready for pickup
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt text-primary me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="{{ route('faculty.requests.create') }}" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        New Request
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('faculty.requests.index') }}" class="btn btn-info btn-lg w-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-list me-2"></i>
                                        My Requests
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('faculty.items.index') }}" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-boxes me-2"></i>
                                        Browse Items
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection