@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-chart-bar me-2 text-warning"></i>
                        Reports & Downloads
                    </h2>
                </div>

                <!-- Quick Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-primary bg-opacity-10">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ number_format($stats['total_items']) }}</h4>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning bg-opacity-10">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ number_format($stats['low_stock_items']) }}</h4>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-info bg-opacity-10">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ number_format($stats['total_requests_this_month']) }}</h4>
                                <small class="text-muted">Requests This Month</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-success bg-opacity-10">
                            <div class="card-body text-center">
                                <i class="fas fa-peso-sign fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">₱{{ number_format($stats['total_value'], 0) }}</h4>
                                <small class="text-muted">Total Value</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row g-4">
                    <!-- Analytics Dashboard -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Visual Analytics
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">View interactive charts and graphs for better insights</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="{{ route('reports.dashboard', ['period' => 'daily']) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-calendar-day me-2"></i>Daily Charts
                                    </a>
                                    <a href="{{ route('reports.dashboard', ['period' => 'weekly']) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-calendar-week me-2"></i>Weekly Charts
                                    </a>
                                    <a href="{{ route('reports.dashboard', ['period' => 'monthly']) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-calendar-alt me-2"></i>Monthly Charts
                                    </a>
                                    <a href="{{ route('reports.dashboard', ['period' => 'annually']) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-calendar me-2"></i>Annual Charts
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Reports -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-download me-2"></i>
                                    Download Reports
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">Download PDF reports for your records</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="{{ route('reports.daily-transactions') }}?format=pdf" class="btn btn-outline-success">
                                        <i class="fas fa-file-pdf me-2"></i>Daily Records PDF
                                    </a>
                                    <a href="{{ route('reports.weekly-summary') }}?format=pdf" class="btn btn-outline-success">
                                        <i class="fas fa-file-pdf me-2"></i>Weekly Records PDF
                                    </a>
                                    <a href="{{ route('reports.monthly-summary') }}?format=pdf" class="btn btn-outline-success">
                                        <i class="fas fa-file-pdf me-2"></i>Monthly Records PDF
                                    </a>
                                    <a href="{{ route('reports.annual-summary') }}?format=pdf" class="btn btn-outline-success">
                                        <i class="fas fa-file-pdf me-2"></i>Annual Records PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Reports -->
                <div class="row g-4 mt-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Other Reports
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <a href="{{ route('reports.inventory-summary') }}" class="btn btn-outline-info w-100">
                                            <i class="fas fa-boxes me-2"></i>Inventory Summary
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{ route('reports.low-stock-alert') }}" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-history me-2"></i>Activity Logs
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
</div>
@endsection
