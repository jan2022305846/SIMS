@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- Dashboard JavaScript -->
    @vite(['resources/js/dashboard.js'])
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Card with QR Scanner -->
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
                                    @else
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            <i class="fas fa-building me-1"></i>
                                            Office Head Dashboard
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted mb-0">
                                    @if(Auth::user()->role === 'admin')
                                        Manage inventory, users, and monitor system activities.
                                    @else
                                        Manage office requests, browse items, and track submissions.
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
                                        <div class="btn-group" role="group">
                                            <button id="start-camera-btn" class="btn btn-warning fw-semibold">
                                                <i class="fas fa-camera me-2"></i>
                                                Camera Scan
                                            </button>
                                            <button id="start-barcode-btn" class="btn btn-outline-warning fw-semibold">
                                                <i class="fas fa-barcode me-2"></i>
                                                Barcode Reader
                                            </button>
                                        </div>
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

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
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
                                    <h2 class="stats-value">{{ number_format($pendingRequests ?? 0) }}</h2>
                                    <small class="text-warning">
                                        <i class="fas fa-hourglass-half me-1"></i>Awaiting approval
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts Card -->
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
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Low Stock Alerts</h6>
                                    <h2 class="stats-value">{{ number_format($lowStockItems ?? 0) }}</h2>
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>Items need attention
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Number of Users Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-users fa-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Users</h6>
                                    <h2 class="stats-value">{{ number_format($totalUsers ?? 0) }}</h2>
                                    <small class="text-info">
                                        <i class="fas fa-user-graduate me-1"></i>Active system users
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items for Disposal Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-secondary bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-trash-alt fa-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Items for Disposal</h6>
                                    <h2 class="stats-value">{{ number_format($expiringItems ?? 0) }}</h2>
                                    <small class="text-secondary">
                                        <i class="fas fa-calendar-times me-1"></i>Expired items
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

        </div>
    </div>
</div>

<!-- Custom Item Details Modal -->
<div id="customItemModal" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <i class="fas fa-box me-2"></i>Item Details
            </h5>
            <button type="button" class="custom-modal-close" onclick="closeCustomModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="custom-modal-body" id="customModalContent">
            <!-- Item details will be loaded here -->
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCustomModal()">
                <i class="fas fa-times me-1"></i>Close
            </button>
        </div>
    </div>
</div>

@endsection