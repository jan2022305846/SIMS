@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>
                        User Activity Report
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Period Selector -->
                        <div class="btn-group" id="periodSelector">
                            <a href="{{ route('reports.user-activity-report', ['period' => 'daily']) }}"
                               class="btn {{ ($period ?? 'annually') === 'daily' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-day me-1"></i>Daily
                            </a>
                            <a href="{{ route('reports.user-activity-report', ['period' => 'weekly']) }}"
                               class="btn {{ ($period ?? 'annually') === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar-week me-1"></i>Weekly
                            </a>
                            <a href="{{ route('reports.user-activity-report', ['period' => 'annually']) }}"
                               class="btn {{ ($period ?? 'annually') === 'annually' ? 'btn-primary' : 'btn-outline-primary' }} period-btn">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </a>
                        </div>
                        <!-- PDF Export -->
                        <a href="{{ route('reports.user-activity-report', ['period' => $period ?? 'annually', 'format' => 'pdf']) }}"
                           class="btn btn-danger btn-sm" download>
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </div>

                <!-- Activity Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-user-check fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ number_format($activityStats['total_activities']) }}</h4>
                                <small class="text-muted">Total Activities</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ number_format($activityStats['unique_users']) }}</h4>
                                <small class="text-muted">Unique Users</small>
                            </div>
                        </div>
                    </div>
                </div>                <!-- Activity Trends Chart -->
                @if(isset($activityStats['daily_activity']) && count($activityStats['daily_activity']) > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Daily Activity Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="activityTrendsChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Most Active Users -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>
                                    Most Active Users
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($activityStats['most_active_users']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Department</th>
                                                    <th class="text-center">Activity Count</th>
                                                    <th class="text-center">Last Activity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activityStats['most_active_users'] as $user)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                                    {{ strtoupper(substr($user['user']->name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <strong>{{ $user['user']->name }}</strong><br>
                                                                    <small class="text-muted">{{ $user['user']->email }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $user['user']->department->name ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success">{{ $user['activity_count'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <small class="text-muted">
                                                                {{ $user['last_activity'] ? \Carbon\Carbon::parse($user['last_activity'])->diffForHumans() : 'Never' }}
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No user activity data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activities by Action -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Activities by Action
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(count($activityStats['activities_by_action']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Action</th>
                                                    <th class="text-end">Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activityStats['activities_by_action'] as $action => $count)
                                                    <tr>
                                                        <td>{{ ucfirst(str_replace('_', ' ', $action)) }}</td>
                                                        <td class="text-end">
                                                            <span class="badge bg-primary">{{ $count }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No activity data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Daily Activity Breakdown
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(count($activityStats['daily_activity']) > 0)
                                    <canvas id="dailyActivityChart" style="max-height: 300px;"></canvas>
                                @else
                                    <p class="text-muted mb-0">No daily activity data available.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Log -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Recent Activity Log
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($logs->count() > 0)
                                    <div class="timeline">
                                        @foreach($logs->take(20) as $activity)
                                            <div class="timeline-item mb-3">
                                                <div class="timeline-marker bg-primary"></div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong>{{ $activity->user->name }}</strong>
                                                            <span class="text-muted ms-2">{{ $activity->action }}</span>
                                                            @if($activity->details)
                                                                <span class="badge bg-light text-dark ms-2">{{ $activity->details }}</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $activity->user->department->name ?? 'N/A' }} •
                                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('M j, Y g:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No recent activities found.</p>
                                @endif
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

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-left: 15px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}
</style>

<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    @if(isset($activityStats['daily_activity']) && count($activityStats['daily_activity']) > 0)
    // Activity Trends Chart
    const trendsCtx = document.getElementById('activityTrendsChart').getContext('2d');
    const dailyActivity = @json($activityStats['daily_activity']);

    const labels = Object.keys(dailyActivity);
    const activityData = labels.map(date => dailyActivity[date]);

    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Activities',
                data: activityData,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
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
    @endif

    @if(count($activityStats['daily_activity']) > 0)
    // Daily Activity Chart
    const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
    const dailyActivityData = @json($activityStats['daily_activity']);

    const dailyLabels = Object.keys(dailyActivityData);
    const dailyCounts = dailyLabels.map(date => dailyActivityData[date]);

    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Activity Count',
                data: dailyCounts,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
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
    @endif
}
</script>
@endsection