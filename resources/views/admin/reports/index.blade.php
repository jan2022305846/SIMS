@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Reports Dashboard
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Period Selector -->
                        <div class="btn-group" id="periodSelector">
                            <a href="{{ route('reports.index', ['period' => 'daily']) }}" 
                               class="btn {{ ($period ?? 'daily') === 'daily' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-day me-1"></i>Daily
                            </a>
                            <a href="{{ route('reports.index', ['period' => 'weekly']) }}" 
                               class="btn {{ ($period ?? 'daily') === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-week me-1"></i>Weekly
                            </a>
                            <a href="{{ route('reports.index', ['period' => 'annually']) }}" 
                               class="btn {{ ($period ?? 'daily') === 'annually' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </a>
                        </div>
                        <!-- Download Button (always visible) -->
                        <button class="btn btn-success" onclick="downloadReport()">
                            <i class="fas fa-download me-1"></i>Download CSV
                        </button>
                    </div>
                </div>

                @if(isset($message))
                <!-- Temporary Message -->
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong>Dashboard Update:</strong> {{ $message }}
                        <br><small>The system is being updated with enhanced features. Please try again in a few minutes.</small>
                    </div>
                </div>
                @endif

                <!-- Stats Overview -->
                <div class="row g-3 mb-4" id="statsContainer">
                    <div class="col-md-3">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ $data['total_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Total Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $data['completed_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ $data['pending_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $data['items_requested'] ?? 0 }}</h4>
                                <small class="text-muted">Items Requested</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Requests Overview - {{ ucfirst($period ?? 'daily') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="requestsChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Value Trends - {{ ucfirst($period ?? 'daily') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="itemsChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Reports Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Additional Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="{{ route('reports.inventory-summary') }}" class="text-decoration-none">
                                    <div class="card border-primary h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                            <h6 class="card-title">Inventory Summary</h6>
                                            <p class="card-text small text-muted">Complete inventory overview with stock levels and values</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.low-stock-alert') }}" class="text-decoration-none">
                                    <div class="card border-warning h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                            <h6 class="card-title">Low Stock Alerts</h6>
                                            <p class="card-text small text-muted">Items running low on stock that need attention</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.qr-scan-analytics') }}" class="text-decoration-none">
                                    <div class="card border-info h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-qrcode fa-2x text-info mb-2"></i>
                                            <h6 class="card-title">QR Scan Analytics</h6>
                                            <p class="card-text small text-muted">Monitor QR code scanning activity and item usage</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.scan-alerts') }}" class="text-decoration-none">
                                    <div class="card border-danger h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-bell fa-2x text-danger mb-2"></i>
                                            <h6 class="card-title">Scan Alerts</h6>
                                            <p class="card-text small text-muted">Items not scanned recently and unusual activity</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.request-analytics') }}" class="text-decoration-none">
                                    <div class="card border-success h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chart-pie fa-2x text-success mb-2"></i>
                                            <h6 class="card-title">Request Analytics</h6>
                                            <p class="card-text small text-muted">Detailed analysis of request patterns and trends</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.user-activity-report') }}" class="text-decoration-none">
                                    <div class="card border-secondary h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-users fa-2x text-secondary mb-2"></i>
                                            <h6 class="card-title">User Activity</h6>
                                            <p class="card-text small text-muted">Track user actions and system usage patterns</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let requestsChart, itemsChart;
let currentPeriod = '{{ $period ?? 'daily' }}';

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($data['chart_data']) && is_array($data['chart_data']) && count($data['chart_data']) > 0)
        initializeCharts(@json($data['chart_data']));
    @else
        initializeEmptyCharts();
    @endif
});

// Initialize charts
function initializeCharts(chartData) {
    // Transform chart data for Chart.js
    const labels = chartData.map(item => item.date);
    const requests = chartData.map(item => item.requests);
    const disbursements = chartData.map(item => item.disbursements);
    const pending = chartData.map(item => 0); // We'll calculate this differently
    const values = chartData.map(item => item.value);

    // Requests Chart
    const requestsCtx = document.getElementById('requestsChart').getContext('2d');
    requestsChart = new Chart(requestsCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Requests',
                    data: requests,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Fulfilled',
                    data: disbursements,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pending',
                    data: pending,
                    backgroundColor: 'rgba(255, 206, 86, 0.7)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            animation: {
                duration: 750
            }
        }
    });

    // Items Chart
    const itemsCtx = document.getElementById('itemsChart').getContext('2d');
    itemsChart = new Chart(itemsCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Value',
                data: values,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            animation: {
                duration: 750
            }
        }
    });
}

// Initialize empty charts
function initializeEmptyCharts() {
    initializeCharts([]);
}

// Download CSV Function
function downloadReport() {
    let url = '';
    const now = new Date();
    
    switch(currentPeriod) {
        case 'daily':
            url = '{{ route("reports.daily-csv") }}?date=' + now.toISOString().split('T')[0];
            break;
        case 'weekly':
            url = '{{ route("reports.weekly-csv") }}?date=' + now.toISOString().split('T')[0];
            break;
        case 'annually':
            url = '{{ route("reports.annual-csv") }}?year=' + now.getFullYear();
            break;
        default:
            url = '{{ route("reports.daily-csv") }}?date=' + now.toISOString().split('T')[0];
    }
    
    window.open(url, '_blank');
}
</script>
@endsection
