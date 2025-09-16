@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    @if(Auth::user()->role !== 'faculty')
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    @endif
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
                        <div class="col-lg-{{ Auth::user()->role === 'faculty' ? '12' : '6' }}">
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
                        
                        <!-- QR Scanner Section - Only for Admin and Office Head -->
                        @if(Auth::user()->role !== 'faculty')
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
                        @endif
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="row g-4 mb-4">
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
                                        <h2 class="stats-value">{{ number_format($totalItems ?? 0) }}</h2>
                                        <small class="text-success">
                                            <i class="fas fa-chart-line me-1"></i>{{ number_format($totalCategories ?? 0) }} categories
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Value Card -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 70px; height: 70px;">
                                            <i class="fas fa-peso-sign fa-xl text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Stock Value</h6>
                                        <h2 class="stats-value">₱{{ number_format($totalStockValue ?? 0, 2) }}</h2>
                                        <small class="text-info">
                                            <i class="fas fa-calculator me-1"></i>Current inventory value
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
                                        <h2 class="stats-value">{{ number_format($pendingRequests ?? 0) }}</h2>
                                        <small class="text-warning">
                                            <i class="fas fa-trending-up me-1"></i>{{ number_format($totalRequestsThisMonth ?? 0) }} this month
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Summary Card -->
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
                                        <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Alerts</h6>
                                        <h2 class="stats-value">{{ number_format(($lowStockItems ?? 0) + ($expiringItems ?? 0)) }}</h2>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $lowStockItems ?? 0 }} low stock, {{ $expiringItems ?? 0 }} expiring
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
                @endif
            </div>

            @if(Auth::user()->role === 'admin')
                <!-- Admin Dashboard - Alerts & Analytics Section -->
                <div class="row g-4 mb-4">
                    <!-- Low Stock Alerts -->
                    <div class="col-xl-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        Low Stock Alerts
                                    </h5>
                                    <a href="{{ route('items.low-stock') }}" class="btn btn-sm btn-outline-warning">
                                        View All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($lowStockAlerts && $lowStockAlerts->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($lowStockAlerts->take(5) as $item)
                                            <div class="list-group-item border-0 px-0 py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold">{{ $item->name }}</h6>
                                                        <small class="text-muted">{{ $item->category->name }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-warning">{{ $item->current_stock }} left</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p class="text-muted mb-0">All items are well stocked!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Items -->
                    <div class="col-xl-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar-times text-danger me-2"></i>
                                        Expiring Soon
                                    </h5>
                                    <a href="{{ route('items.expiring-soon') }}" class="btn btn-sm btn-outline-danger">
                                        View All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($expiringSoonItems && $expiringSoonItems->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($expiringSoonItems->take(5) as $item)
                                            <div class="list-group-item border-0 px-0 py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold">{{ $item->name }}</h6>
                                                        <small class="text-muted">{{ $item->category->name }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        @php
                                                            $daysLeft = \Carbon\Carbon::now()->diffInDays($item->expiry_date, false);
                                                        @endphp
                                                        <span class="badge bg-danger">
                                                            {{ $daysLeft <= 0 ? 'Expired' : $daysLeft . ' days' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                                        <p class="text-muted mb-0">No items expiring soon!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="row g-4 mb-4">
                    <!-- Weekly Request Trends -->
                    <div class="col-xl-8">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line text-primary me-2"></i>
                                    Weekly Request Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="requestTrendsChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top Categories -->
                    <div class="col-xl-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tags text-info me-2"></i>
                                    Top Categories
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($topCategories && $topCategories->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($topCategories as $category)
                                            <div class="list-group-item border-0 px-0 py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 fw-semibold">{{ $category->name }}</h6>
                                                    </div>
                                                    <span class="badge bg-info">{{ $category->items_count }} items</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No categories found</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
