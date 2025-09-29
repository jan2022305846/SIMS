@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-qrcode me-2 text-primary"></i>
                        QR Code Scan Analytics
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Period Selector -->
                        <div class="btn-group" id="periodSelector">
                            <a href="{{ route('reports.qr-scan-analytics', ['period' => 'daily']) }}"
                               class="btn {{ ($period ?? 'daily') === 'daily' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-day me-1"></i>Daily
                            </a>
                            <a href="{{ route('reports.qr-scan-analytics', ['period' => 'weekly']) }}"
                               class="btn {{ ($period ?? 'daily') === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-week me-1"></i>Weekly
                            </a>
                            <a href="{{ route('reports.qr-scan-analytics', ['period' => 'annually']) }}"
                               class="btn {{ ($period ?? 'daily') === 'annually' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </a>
                        </div>
                        <!-- PDF Export -->
                        <a href="{{ route('reports.qr-scan-analytics', ['period' => $period ?? 'daily', 'format' => 'pdf']) }}"
                           class="btn btn-danger btn-sm" download>
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-qrcode fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['total_scans']) }}</h4>
                                <small class="text-muted">Total Scans</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['unique_items_scanned']) }}</h4>
                                <small class="text-muted">Items Scanned</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['unique_users_scanning']) }}</h4>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['unscanned_items']) }}</h4>
                                <small class="text-muted">Unscanned (30+ days)</small>
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
                                    Daily Scan Activity - {{ ucfirst($period ?? 'daily') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="scanActivityChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Scans by Scanner Type
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="scannerTypeChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Analytics -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>
                                    Most Scanned Items
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($data['analytics']['most_scanned_items']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Scan Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['analytics']['most_scanned_items'] as $item)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('reports.item-scan-history', $item['item']->id) }}" class="text-decoration-none">
                                                                {{ $item['item']->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $item['item']->category->name ?? 'N/A' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $item['scan_count'] }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No scan data available for the selected period.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    Scans by Location
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($data['analytics']['scans_by_location']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Location</th>
                                                    <th class="text-center">Scan Count</th>
                                                    <th class="text-center">Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['analytics']['scans_by_location'] as $location => $count)
                                                    <tr>
                                                        <td>{{ $location ?: 'Unknown' }}</td>
                                                        <td class="text-center">{{ $count }}</td>
                                                        <td class="text-center">
                                                            {{ number_format(($count / $data['summary']['total_scans']) * 100, 1) }}%
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No location data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Scan Logs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>
                            Recent Scan Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($data['records']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Item</th>
                                            <th>User</th>
                                            <th>Location</th>
                                            <th>Scanner Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['records'] as $log)
                                            <tr>
                                                <td>{{ $log->scanned_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('reports.item-scan-history', $log->item_id) }}" class="text-decoration-none">
                                                        {{ $log->item->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $log->user->name ?? 'System' }}</td>
                                                <td>{{ $log->location ?: 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ ucfirst($log->scanner_type) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No scan activity found for the selected period.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Daily Scan Activity Chart
    const scanActivityCtx = document.getElementById('scanActivityChart').getContext('2d');
    const scanData = @json($data['chart_data']);

    new Chart(scanActivityCtx, {
        type: 'line',
        data: {
            labels: scanData.map(item => item.date),
            datasets: [{
                label: 'Daily Scans',
                data: scanData.map(item => item.scans),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
            }
        }
    });

    // Scanner Type Chart
    const scannerTypeCtx = document.getElementById('scannerTypeChart').getContext('2d');
    const scannerData = @json($data['analytics']['scans_by_scanner_type']);

    new Chart(scannerTypeCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(scannerData).map(type => type.charAt(0).toUpperCase() + type.slice(1)),
            datasets: [{
                data: Object.values(scannerData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>
@endsection