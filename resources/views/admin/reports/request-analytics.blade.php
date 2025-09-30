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
                        Request Analytics
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Period Selector -->
                        <div class="btn-group" id="periodSelector">
                            <a href="{{ route('reports.request-analytics', ['period' => 'daily']) }}"
                               class="btn {{ ($period ?? 'daily') === 'daily' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-day me-1"></i>Daily
                            </a>
                            <a href="{{ route('reports.request-analytics', ['period' => 'weekly']) }}"
                               class="btn {{ ($period ?? 'daily') === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-week me-1"></i>Weekly
                            </a>
                            <a href="{{ route('reports.request-analytics', ['period' => 'annually']) }}"
                               class="btn {{ ($period ?? 'daily') === 'annually' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </a>
                        </div>
                        <!-- PDF Export -->
                        <a href="{{ route('reports.request-analytics', ['period' => $period ?? 'daily', 'format' => 'pdf']) }}"
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
                                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['total_requests']) }}</h4>
                                <small class="text-muted">Total Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['approved_requests']) }}</h4>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['pending_requests']) }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0">{{ number_format($data['summary']['declined_requests']) }}</h4>
                                <small class="text-muted">Declined</small>
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
                                    Request Activity - {{ ucfirst($period ?? 'daily') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="requestActivityChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Requests by Department
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart" style="max-height: 300px;"></canvas>
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
                                    Most Requested Items
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($data['analytics']['most_requested_items']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Total Qty</th>
                                                    <th class="text-center">Requests</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['analytics']['most_requested_items'] as $item)
                                                    <tr>
                                                        <td>{{ $item['item']->name }}</td>
                                                        <td>{{ $item['item']->category->name ?? 'N/A' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">{{ $item['total_quantity'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">{{ $item['request_count'] }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No request data available for the selected period.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-building me-2"></i>
                                    Requests by Department
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($data['analytics']['requests_by_department']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Department</th>
                                                    <th class="text-center">Request Count</th>
                                                    <th class="text-center">Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['analytics']['requests_by_department'] as $department => $count)
                                                    <tr>
                                                        <td>{{ $department ?: 'Unspecified' }}</td>
                                                        <td class="text-center">{{ $count }}</td>
                                                        <td class="text-center">
                                                            {{ number_format(($count / $data['summary']['total_requests']) * 100, 1) }}%
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No department data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>
                            Recent Requests
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
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['records'] as $request)
                                            <tr>
                                                <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                                <td>{{ $request->item->name }}</td>
                                                <td>{{ $request->user->name ?? 'N/A' }}</td>
                                                <td>{{ $request->department ?: 'N/A' }}</td>
                                                <td>
                                                    @if($request->status == 'approved_by_admin' || $request->status == 'fulfilled' || $request->status == 'claimed')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($request->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($request->status == 'declined_by_office_head' || $request->status == 'declined_by_admin')
                                                        <span class="badge bg-danger">Declined</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($request->priority == 'high')
                                                        <span class="badge bg-danger">High</span>
                                                    @elseif($request->priority == 'medium')
                                                        <span class="badge bg-warning">Medium</span>
                                                    @elseif($request->priority == 'low')
                                                        <span class="badge bg-success">Low</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($request->priority ?? 'normal') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No requests found for the selected period.</p>
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
    // Request Activity Chart
    const requestActivityCtx = document.getElementById('requestActivityChart').getContext('2d');
    const requestData = @json($data['chart_data']);

    new Chart(requestActivityCtx, {
        type: 'line',
        data: {
            labels: requestData.map(item => item.date),
            datasets: [{
                label: 'Daily Requests',
                data: requestData.map(item => item.requests),
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

    // Department Chart
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const departmentData = @json($data['analytics']['requests_by_department']);

    new Chart(departmentCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(departmentData).map(dept => dept || 'Unspecified'),
            datasets: [{
                data: Object.values(departmentData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
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