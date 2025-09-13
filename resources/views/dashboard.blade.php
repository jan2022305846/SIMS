@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- Dashboard JavaScript -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Card with QR Scanner - Enhanced for sidebar layout -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <!-- Welcome Section -->
                        <div class="col-lg-6">
                            <div class="pe-lg-4">
                                <h2 class="fw-bold mb-3">
                                    <i class="fas fa-tachometer-alt me-2" style="color: var(--accent-primary);"></i>
                                    Welcome back, {{ Auth::user()->name }}!
                                </h2>
                                <div class="mb-3">
                                    @if(Auth::user()->role === 'admin')
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Admin Dashboard
                                        </span>
                                    @elseif(Auth::user()->role === 'office_head')
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="fas fa-building me-1"></i>
                                            Office Head Dashboard
                                        </span>
                                    @else
                                        <span class="badge bg-warning fs-6 px-3 py-2">
                                            <i class="fas fa-user-graduate me-1"></i>
                                            Faculty Dashboard
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted mb-0">
                                    @if(Auth::user()->role === 'admin')
                                        Manage inventory, users, and monitor system activities.
                                    @elseif(Auth::user()->role === 'office_head')
                                        Manage office requests, browse items, and track submissions.
                                    @else
                                        Browse items, create requests, and track your submissions.
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- QR Scanner Section -->
                        <div class="col-lg-6">
                            <div class="ps-lg-4 border-start border-2">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-qrcode fa-2x me-3" style="color: var(--accent-primary);"></i>
                                    <div>
                                        <h4 class="h5 fw-bold mb-1">QR Code Scanner</h4>
                                        <p class="text-muted small mb-0">Scan QR codes for instant item access</p>
                                    </div>
                                </div>
                                
                                <div id="qr-scanner-container">
                                    <div class="text-center mb-3">
                                        <button id="start-scanner-btn" class="btn btn-warning btn-lg fw-semibold">
                                            <i class="fas fa-camera me-2"></i>
                                            Start Scanner
                                        </button>
                                    </div>
                                    <div id="qr-reader" style="display: none; width: 100%; max-width: 300px; margin: 15px auto;"></div>
                                    <div id="scan-result" class="mt-3" style="display: none;">
                                        <div class="alert alert-success d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <div>
                                                <strong class="small">Scan Successful!</strong>
                                                <div id="scan-details" class="small mt-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards - Enhanced and Bigger -->
            <div class="row g-4">
                @if(Auth::user()->role === 'admin')
                    <!-- Total Items Card -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 70px; height: 70px;">
                                            <i class="fas fa-box fa-xl text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Items</h6>
                                        <h2 class="stats-value">{{ $totalItems ?? 0 }}</h2>
                                        <small class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>Active inventory
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users Card -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 70px; height: 70px;">
                                            <i class="fas fa-users fa-xl text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Users</h6>
                                        <h2 class="stats-value">{{ $totalUsers ?? 0 }}</h2>
                                        <small class="text-info">
                                            <i class="fas fa-user-check me-1"></i>Registered members
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Requests Card -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
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
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Requests</h6>
                                        <h2 class="stats-value">{{ $pendingRequests ?? 0 }}</h2>
                                        <small class="text-warning">
                                            <i class="fas fa-hourglass-half me-1"></i>Awaiting approval
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Items Card -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-danger bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 70px; height: 70px;">
                                            <i class="fas fa-exclamation-triangle fa-xl text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Low Stock Alert</h6>
                                        <h2 class="stats-value">{{ $lowStockItems ?? 0 }}</h2>
                                        <small class="text-danger">
                                            <i class="fas fa-arrow-down me-1"></i>Need restocking
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Faculty/Office Head Stats -->
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
                                        <h2 class="stats-value">{{ $myRequests ?? 0 }}</h2>
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
                                        <h2 class="stats-value">{{ $myPendingRequests ?? 0 }}</h2>
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
                                        <h2 class="stats-value">{{ $myApprovedRequests ?? 0 }}</h2>
                                        <small class="text-success">
                                            <i class="fas fa-thumbs-up me-1"></i>Ready for pickup
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
