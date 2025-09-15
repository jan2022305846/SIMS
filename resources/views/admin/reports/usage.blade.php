@extends('layouts.app')

@section('title', 'Usage Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Usage Reports & Analytics</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Usage Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.usage') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="period" class="form-label">Time Period</label>
                            <select name="period" id="period" class="form-select">
                                <option value="last_7_days" {{ request('period') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="last_30_days" {{ request('period', 'last_30_days') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="last_90_days" {{ request('period') == 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                                <option value="last_6_months" {{ request('period') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                <option value="last_year" {{ request('period') == 'last_year' ? 'selected' : '' }}>Last Year</option>
                                <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="limit" class="form-label">Show Items</label>
                            <select name="limit" id="limit" class="form-select">
                                <option value="10" {{ request('limit') == '10' ? 'selected' : '' }}>Top 10</option>
                                <option value="20" {{ request('limit', '20') == '20' ? 'selected' : '' }}>Top 20</option>
                                <option value="50" {{ request('limit') == '50' ? 'selected' : '' }}>Top 50</option>
                                <option value="100" {{ request('limit') == '100' ? 'selected' : '' }}>Top 100</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('reports.usage.export', request()->query()) }}" class="btn btn-success">
                                    <i class="fas fa-download me-1"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Requests</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $stats['total_requests'] }}">{{ $stats['total_requests'] }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary-subtle">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="fas fa-shopping-cart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Items Requested</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $stats['total_items_requested'] }}">{{ $stats['total_items_requested'] }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info-subtle">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="fas fa-box"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Avg Requests/Day</span>
                            <h4 class="mb-3">
                                <span class="counter-value" data-target="{{ $stats['average_requests_per_day'] }}">{{ $stats['average_requests_per_day'] }}</span>
                            </h4>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning-subtle">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Top Item</span>
                            <h6 class="mb-3 text-truncate">
                                {{ $stats['top_requested_item'] ? $stats['top_requested_item']->name : 'N/A' }}
                            </h6>
                            @if($stats['top_requested_item'])
                                <p class="text-muted mb-0">{{ $stats['top_requested_count'] }} requests</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success-subtle">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="fas fa-trophy"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Usage Trends</h4>
                </div>
                <div class="card-body">
                    <canvas id="usageTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Category Usage</h4>
                </div>
                <div class="card-body">
                    <canvas id="categoryUsageChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Requested Items -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1">Most Requested Items</h4>
                        <div class="flex-shrink-0">
                            <span class="badge bg-success">{{ $analytics['most_requested']->count() }} items</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($analytics['most_requested']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-nowrap table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Rank</th>
                                        <th scope="col">Item</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Request Count</th>
                                        <th scope="col">Total Quantity</th>
                                        <th scope="col">Avg per Request</th>
                                        <th scope="col">Usage Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['most_requested'] as $index => $data)
                                        <tr>
                                            <td>
                                                @if($index < 3)
                                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'info' : 'secondary') }}">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">#{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0">{{ $data['item']->name }}</h6>
                                                        <small class="text-muted">{{ $data['item']->description ?? 'No description' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $data['item']->category->name }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $data['request_count'] }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ number_format($data['total_quantity']) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $data['average_per_request'] }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: {{ min(100, ($data['usage_score'] / max(1, $analytics['most_requested']->first()['usage_score'])) * 100) }}%">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="ms-2 text-muted small">{{ number_format($data['usage_score'], 1) }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar text-muted" style="font-size: 48px;"></i>
                            <h5 class="mt-3">No usage data found</h5>
                            <p class="text-muted">No requests found for the selected period and filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Least Requested Items -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1">Least Requested Items</h4>
                        <div class="flex-shrink-0">
                            <span class="badge bg-warning">{{ $analytics['least_requested']->count() }} items</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($analytics['least_requested']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-nowrap table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Request Count</th>
                                        <th scope="col">Total Quantity</th>
                                        <th scope="col">Last Request</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['least_requested'] as $data)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0">{{ $data['item']->name }}</h6>
                                                        <small class="text-muted">{{ $data['item']->description ?? 'No description' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $data['item']->category->name }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $data['request_count'] == 0 ? 'text-danger' : 'text-warning' }}">
                                                    {{ $data['request_count'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ number_format($data['total_quantity']) }}</span>
                                            </td>
                                            <td>
                                                @if($data['days_since_last_request'] === null)
                                                    <span class="badge bg-danger">Never</span>
                                                @elseif($data['days_since_last_request'] > 365)
                                                    <span class="badge bg-danger">{{ number_format($data['days_since_last_request'] / 365, 1) }} years ago</span>
                                                @elseif($data['days_since_last_request'] > 30)
                                                    <span class="badge bg-warning">{{ number_format($data['days_since_last_request'] / 30, 1) }} months ago</span>
                                                @else
                                                    <span class="badge bg-info">{{ $data['days_since_last_request'] }} days ago</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($data['request_count'] == 0)
                                                    <span class="badge bg-danger">Unused</span>
                                                @elseif($data['days_since_last_request'] > 180)
                                                    <span class="badge bg-warning">Rarely Used</span>
                                                @else
                                                    <span class="badge bg-info">Low Usage</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-smile text-success" style="font-size: 48px;"></i>
                            <h5 class="mt-3">All items are being used!</h5>
                            <p class="text-muted">No items found with low usage in the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Usage Trends Chart
    const trendCtx = document.getElementById('usageTrendsChart').getContext('2d');
    const trendData = @json($analytics['usage_trends']);
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(item => item.date),
            datasets: [{
                label: 'Request Count',
                data: trendData.map(item => item.request_count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Total Quantity',
                data: trendData.map(item => item.total_quantity),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Request Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Total Quantity'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Usage Trends Over Time'
                }
            }
        }
    });

    // Category Usage Chart
    const categoryCtx = document.getElementById('categoryUsageChart').getContext('2d');
    const categoryData = @json($analytics['category_usage']);
    
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category_name),
            datasets: [{
                data: categoryData.map(item => item.request_count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ],
                hoverBackgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#C9CBCF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Requests by Category'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const category = categoryData[context.dataIndex];
                            return `${context.label}: ${context.parsed} requests (${category.total_quantity} items)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
