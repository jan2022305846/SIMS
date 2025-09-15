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
                            <button type="button" class="btn btn-primary period-btn" data-period="daily">
                                <i class="fas fa-calendar-day me-1"></i>Daily
                            </button>
                            <button type="button" class="btn btn-outline-primary period-btn" data-period="weekly">
                                <i class="fas fa-calendar-week me-1"></i>Weekly
                            </button>
                            <button type="button" class="btn btn-outline-primary period-btn" data-period="monthly">
                                <i class="fas fa-calendar-alt me-1"></i>Monthly
                            </button>
                            <button type="button" class="btn btn-outline-primary period-btn" data-period="annually">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </button>
                        </div>
                        <!-- Download Button -->
                        <button class="btn btn-success" onclick="downloadReport()">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                        <!-- Loading Indicator -->
                        <div class="spinner-border spinner-border-sm text-primary d-none" id="loadingSpinner" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
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
                                <h4 class="mb-0" id="totalRequests">{{ $data['total_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0" id="completedRequests">{{ $data['completed_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0" id="pendingRequests">{{ $data['pending_requests'] ?? 0 }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                <h4 class="mb-0" id="itemsRequested">{{ $data['items_requested'] ?? 0 }}</h4>
                                <small class="text-muted">Items</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4">
                    <!-- Requests Chart -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Requests Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="requestsChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Items Chart -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Items Trend
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="itemsChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>
                                    <span id="periodTitle">{{ ucfirst($period) }}</span> Records
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th>Requests</th>
                                                <th>Completed</th>
                                                <th>Pending</th>
                                                <th>Items</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dataTableBody">
                                            @if(isset($data['chart_data']['labels']))
                                                @foreach($data['chart_data']['labels'] as $index => $label)
                                                <tr>
                                                    <td>{{ $label }}</td>
                                                    <td>{{ $data['chart_data']['requests'][$index] ?? 0 }}</td>
                                                    <td>{{ $data['chart_data']['completed'][$index] ?? 0 }}</td>
                                                    <td>{{ $data['chart_data']['pending'][$index] ?? 0 }}</td>
                                                    <td>{{ $data['chart_data']['items'][$index] ?? 0 }}</td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No data available</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Downloads -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-download me-2"></i>
                                    Quick Downloads
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <a href="{{ route('reports.daily-transactions') }}?format=pdf" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-file-pdf me-2"></i>Daily Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('reports.weekly-summary') }}?format=pdf" class="btn btn-outline-success w-100">
                                            <i class="fas fa-file-pdf me-2"></i>Weekly Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('reports.monthly-summary') }}?format=pdf" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-file-pdf me-2"></i>Monthly Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('reports.annual-summary') }}?format=pdf" class="btn btn-outline-info w-100">
                                            <i class="fas fa-file-pdf me-2"></i>Annual Report
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let requestsChart, itemsChart;
let currentPeriod = '{{ $period }}';

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($data['chart_data']))
        initializeCharts(@json($data['chart_data']));
    @else
        initializeEmptyCharts();
    @endif
    setupPeriodButtons();
});

// Setup period button click handlers
function setupPeriodButtons() {
    const buttons = document.querySelectorAll('.period-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            if (period !== currentPeriod) {
                switchPeriod(period);
            }
        });
    });
}

// Switch period and update data
async function switchPeriod(period) {
    // Show loading
    showLoading(true);
    
    // Update button states
    updateButtonStates(period);
    
    try {
        // Fetch new data
        const response = await fetch(`/reports/dashboard-data?period=${period}`);
        const data = await response.json();
        
        // Update everything
        updateStats(data);
        updateCharts(data.chart_data);
        updateTable(data.chart_data, period);
        
        currentPeriod = period;
        
    } catch (error) {
        console.error('Error fetching data:', error);
        alert('Error loading data. Please try again.');
    } finally {
        showLoading(false);
    }
}

// Update button states
function updateButtonStates(activePeriod) {
    const buttons = document.querySelectorAll('.period-btn');
    
    buttons.forEach(button => {
        const period = button.getAttribute('data-period');
        if (period === activePeriod) {
            button.className = 'btn btn-primary period-btn';
        } else {
            button.className = 'btn btn-outline-primary period-btn';
        }
    });
}

// Update stats cards
function updateStats(data) {
    document.getElementById('totalRequests').textContent = data.total_requests || 0;
    document.getElementById('completedRequests').textContent = data.completed_requests || 0;
    document.getElementById('pendingRequests').textContent = data.pending_requests || 0;
    document.getElementById('itemsRequested').textContent = data.items_requested || 0;
}

// Update charts
function updateCharts(chartData) {
    // Update requests chart
    requestsChart.data.labels = chartData.labels || [];
    requestsChart.data.datasets[0].data = chartData.requests || [];
    requestsChart.data.datasets[1].data = chartData.completed || [];
    requestsChart.data.datasets[2].data = chartData.pending || [];
    requestsChart.update('active');
    
    // Update items chart
    itemsChart.data.labels = chartData.labels || [];
    itemsChart.data.datasets[0].data = chartData.items || [];
    itemsChart.update('active');
}

// Update data table
function updateTable(chartData, period) {
    const tableBody = document.getElementById('dataTableBody');
    const periodTitle = document.getElementById('periodTitle');
    
    // Update title
    periodTitle.textContent = period.charAt(0).toUpperCase() + period.slice(1);
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Add new rows
    if (chartData.labels && chartData.labels.length > 0) {
        chartData.labels.forEach((label, index) => {
            const row = `
                <tr>
                    <td>${label}</td>
                    <td>${chartData.requests[index] || 0}</td>
                    <td>${chartData.completed[index] || 0}</td>
                    <td>${chartData.pending[index] || 0}</td>
                    <td>${chartData.items[index] || 0}</td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    } else {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data available</td></tr>';
    }
}

// Initialize charts
function initializeCharts(chartData) {
    // Requests Chart
    const requestsCtx = document.getElementById('requestsChart').getContext('2d');
    requestsChart = new Chart(requestsCtx, {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: [
                {
                    label: 'Total Requests',
                    data: chartData.requests || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: chartData.completed || [],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pending',
                    data: chartData.pending || [],
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
            labels: chartData.labels || [],
            datasets: [{
                label: 'Items Requested',
                data: chartData.items || [],
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
    initializeCharts({
        labels: [],
        requests: [],
        completed: [],
        pending: [],
        items: []
    });
}

// Show/hide loading indicator
function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    const buttons = document.querySelectorAll('.period-btn');
    
    if (show) {
        spinner.classList.remove('d-none');
        buttons.forEach(btn => btn.disabled = true);
    } else {
        spinner.classList.add('d-none');
        buttons.forEach(btn => btn.disabled = false);
    }
}

// Download Function
function downloadReport() {
    let url = '';
    
    switch(currentPeriod) {
        case 'daily':
            url = '{{ route("reports.daily-transactions") }}?format=pdf';
            break;
        case 'weekly':
            url = '{{ route("reports.weekly-summary") }}?format=pdf';
            break;
        case 'monthly':
            url = '{{ route("reports.monthly-summary") }}?format=pdf';
            break;
        case 'annually':
            url = '{{ route("reports.annual-summary") }}?format=pdf';
            break;
        default:
            url = '{{ route("reports.daily-transactions") }}?format=pdf';
    }
    
    window.open(url, '_blank');
}
</script>
@endsection
