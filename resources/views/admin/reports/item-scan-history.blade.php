@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Scan History: {{ $item->name }}
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- Date Range Filter -->
                        <form method="GET" class="d-flex gap-2">
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" style="width: 140px;">
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" style="width: 140px;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </form>
                        <!-- PDF Export -->
                        <a href="{{ route('reports.item-scan-history', ['itemId' => $item->id, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'format' => 'pdf']) }}"
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <!-- Back to Analytics -->
                        <a href="{{ route('reports.qr-scan-analytics') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Analytics
                        </a>
                    </div>
                </div>

                <!-- Item Info Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="text-muted mb-2">{{ $item->description ?? 'No description available' }}</p>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Category</small>
                                        <p class="mb-1">{{ $item->category->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Current Stock</small>
                                        <p class="mb-1">{{ $item->current_stock }} {{ $item->unit }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-primary mb-0">{{ $analytics['total_scans'] }}</h4>
                                            <small class="text-muted">Total Scans</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-info mb-0">{{ $analytics['unique_users'] }}</h4>
                                            <small class="text-muted">Unique Users</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded p-2">
                                            <h4 class="text-success mb-0">{{ $analytics['frequency_analysis']['average_days_between_scans'] ?? 0 }}</h4>
                                            <small class="text-muted">Avg Days Between Scans</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Frequency Analysis -->
                @if($analytics['frequency_analysis']['total_scans'] > 1)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Scan Frequency Analysis
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">First Scan</h6>
                                    <p class="mb-0">{{ $analytics['first_scan'] ? $analytics['first_scan']->format('M d, Y') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Last Scan</h6>
                                    <p class="mb-0">{{ $analytics['last_scan'] ? $analytics['last_scan']->format('M d, Y') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Avg Days Between Scans</h6>
                                    <p class="mb-0">{{ number_format($analytics['frequency_analysis']['average_days_between_scans'], 1) }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Scan Range</h6>
                                    <p class="mb-0">{{ $analytics['frequency_analysis']['min_days_between_scans'] ?? 0 }} - {{ $analytics['frequency_analysis']['max_days_between_scans'] ?? 0 }} days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Location Analysis -->
                @if($analytics['scans_by_location']->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Scans by Location
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($analytics['scans_by_location'] as $location => $count)
                                <div class="col-md-3 mb-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="text-primary">{{ $count }}</h5>
                                            <small class="text-muted">{{ $location ?: 'Unknown Location' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Scan History Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Detailed Scan History
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($scanLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>User</th>
                                            <th>Location</th>
                                            <th>Scanner Type</th>
                                            <th>IP Address</th>
                                            <th>User Agent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($scanLogs as $log)
                                            <tr>
                                                <td>
                                                    <strong>{{ $log->scanned_at->format('M d, Y') }}</strong><br>
                                                    <small class="text-muted">{{ $log->scanned_at->format('H:i:s') }}</small>
                                                </td>
                                                <td>{{ $log->user->name ?? 'System' }}</td>
                                                <td>{{ $log->location ?: 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ ucfirst($log->scanner_type) }}</span>
                                                </td>
                                                <td>
                                                    <code class="small">{{ $log->ip_address ?: 'N/A' }}</code>
                                                </td>
                                                <td>
                                                    <small class="text-muted" title="{{ $log->user_agent }}">
                                                        {{ Str::limit($log->user_agent, 30) }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($scanLogs->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $scanLogs->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Scan History</h5>
                                <p class="text-muted">This item hasn't been scanned yet in the selected date range.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection