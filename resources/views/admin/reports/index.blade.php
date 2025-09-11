@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-chart-bar me-2 text-warning"></i>
                        Reports & Analytics
                    </h2>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>
                                Quick Downloads
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header">Daily Reports</h6></li>
                                <li><a class="dropdown-item" href="{{ route('reports.daily-transactions') }}?format=pdf">
                                    <i class="fas fa-calendar-day me-2"></i>Today's Transactions
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.daily-disbursement') }}?format=pdf">
                                    <i class="fas fa-hand-holding me-2"></i>Daily Disbursement
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Weekly Reports</h6></li>
                                <li><a class="dropdown-item" href="{{ route('reports.weekly-summary') }}?format=pdf">
                                    <i class="fas fa-calendar-week me-2"></i>Weekly Summary
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.weekly-requests') }}?format=pdf">
                                    <i class="fas fa-clipboard-list me-2"></i>Weekly Requests
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Monthly/Annual</h6></li>
                                <li><a class="dropdown-item" href="{{ route('reports.monthly-summary') }}?format=pdf">
                                    <i class="fas fa-calendar-alt me-2"></i>Monthly Report
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.annual-summary') }}?format=pdf">
                                    <i class="fas fa-calendar me-2"></i>Annual Report
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('reports.custom-report') }}" class="btn btn-primary fw-bold">
                            <i class="fas fa-cog me-1"></i>
                            Custom Report
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6 col-lg-3">
                                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-boxes fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title h2 mb-0">{{ number_format($stats['total_items']) }}</h5>
                                            <p class="card-text text-muted mb-0 small">Total Items</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="card border-0 bg-warning bg-opacity-10 h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="bg-warning bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title h2 mb-0">{{ number_format($stats['low_stock_items']) }}</h5>
                                            <p class="card-text text-muted mb-0 small">Low Stock Items</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="card border-0 bg-info bg-opacity-10 h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="bg-info bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-clipboard-list fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title h2 mb-0">{{ number_format($stats['total_requests_this_month']) }}</h5>
                                            <p class="card-text text-muted mb-0 small">Requests This Month</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="card border-0 bg-success bg-opacity-10 h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="bg-success bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-peso-sign fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title h2 mb-0">₱{{ number_format($stats['total_value'], 0) }}</h5>
                                            <p class="card-text text-muted mb-0 small">Total Inventory Value</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reports Grid -->
                        <div class="row g-4">
                            <!-- Inventory Reports -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-boxes me-2"></i>
                                            Inventory Reports
                                        </h5>
                                        <p class="card-text mb-0 small opacity-75">Comprehensive inventory analysis and tracking</p>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="{{ route('reports.inventory-summary') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-chart-bar text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Inventory Summary</div>
                                                        <small class="text-muted">Complete overview of all items and stock levels</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                            <a href="{{ route('reports.low-stock-alert') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Low Stock Alert</div>
                                                        <small class="text-muted">Items that need immediate restocking</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-warning">{{ $stats['low_stock_items'] }}</span>
                                                    <i class="fas fa-chevron-right text-muted"></i>
                                                </div>
                                            </a>
                                            <a href="{{ route('reports.financial-summary') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-peso-sign text-success"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Financial Summary</div>
                                                        <small class="text-muted">Inventory valuation and financial analysis</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Request & Transaction Reports -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-exchange-alt me-2"></i>
                                            Transaction Reports
                                        </h5>
                                        <p class="card-text mb-0 small opacity-75">Request analytics and disbursement tracking</p>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <a href="{{ route('reports.request-analytics') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-chart-line text-info"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Request Analytics</div>
                                                        <small class="text-muted">Comprehensive analysis of all requests</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                            <a href="{{ route('reports.department-report') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-building text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Department Report</div>
                                                        <small class="text-muted">Department-wise request analysis</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                            <a href="{{ route('reports.user-activity') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-users text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">User Activity Report</div>
                                                        <small class="text-muted">Track user activities and engagement</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                            <a href="{{ route('activity-logs.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-purple bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-history text-purple"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">Activity Logs & Audit Trail</div>
                                                        <small class="text-muted">Complete system activity tracking and audit logs</small>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </div>

                        <!-- Quick Actions Section -->
                        <div class="row g-4 mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>
                                            Quick Actions & Custom Reports
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <a href="{{ route('reports.custom-report') }}" class="btn btn-outline-primary w-100 p-4 h-100 d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-cog fa-2x"></i>
                                                    </div>
                                                    <div class="text-start">
                                                        <h6 class="mb-1">Custom Report Builder</h6>
                                                        <small class="text-muted">Create custom reports by combining multiple data sources</small>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="d-grid gap-2">
                                                    <h6 class="mb-3">Quick PDF Downloads</h6>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('reports.inventory-summary', ['format' => 'pdf']) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-pdf me-1"></i>Inventory
                                                        </a>
                                                        <a href="{{ route('reports.low-stock-alert', ['format' => 'pdf']) }}" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-file-pdf me-1"></i>Low Stock
                                                        </a>
                                                        <a href="{{ route('reports.request-analytics', ['format' => 'pdf']) }}" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-file-pdf me-1"></i>Requests
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Stats Overview -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0">
                                    <div class="card-header bg-white">
                                        <h6 class="card-title mb-0 text-muted">Additional Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center g-3">
                                            <div class="col-6 col-lg-2">
                                                <div class="border-end">
                                                    <h4 class="mb-0 text-primary">{{ $stats['total_items'] }}</h4>
                                                    <small class="text-muted">Total Items</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <div class="border-end">
                                                    <h4 class="mb-0 text-warning">{{ $stats['low_stock_items'] }}</h4>
                                                    <small class="text-muted">Low Stock</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <div class="border-end">
                                                    <h4 class="mb-0 text-info">{{ $stats['total_requests_this_month'] }}</h4>
                                                    <small class="text-muted">Monthly Requests</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <div class="border-end">
                                                    <h4 class="mb-0 text-danger">{{ $stats['pending_requests'] }}</h4>
                                                    <small class="text-muted">Pending</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <div class="border-end">
                                                    <h4 class="mb-0 text-secondary">{{ $stats['categories_count'] }}</h4>
                                                    <small class="text-muted">Categories</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <h4 class="mb-0 text-success">₱{{ number_format($stats['total_value'], 0) }}</h4>
                                                <small class="text-muted">Total Value</small>
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
@endsection

<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
    .text-purple {
        color: #8b5cf6 !important;
    }
</style>
