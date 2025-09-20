@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        QR Scan Alerts & Monitoring
                    </h2>
                    <div class="d-flex gap-2">
                        <!-- PDF Export -->
                        <a href="{{ route('reports.scan-alerts', ['format' => 'pdf']) }}"
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <!-- Refresh -->
                        <button onclick="window.location.reload()" class="btn btn-primary btn-sm">
                            <i class="fas fa-sync me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Alert Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0">{{ $stats['total_alerts'] }}</h4>
                                <small class="text-muted">Total Alerts</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ $stats['unscanned_30_days'] }}</h4>
                                <small class="text-muted">Unscanned (30+ days)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-calendar-times fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $stats['unscanned_60_days'] }}</h4>
                                <small class="text-muted">Unscanned (60+ days)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-secondary">
                            <div class="card-body">
                                <i class="fas fa-calendar-alt fa-2x text-secondary mb-2"></i>
                                <h4 class="mb-0">{{ $stats['unscanned_90_days'] }}</h4>
                                <small class="text-muted">Unscanned (90+ days)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unusual Scan Activity -->
                @if(isset($alerts['unusual_scan_activity']) && $alerts['unusual_scan_activity']->count() > 0)
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Unusual Scan Activity Detected
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>Alert:</strong> The following items have been scanned more than 10 times in a single day, which may indicate unusual activity.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Daily Scans</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alerts['unusual_scan_activity'] as $alert)
                                        <tr>
                                            <td>
                                                <a href="{{ route('reports.item-scan-history', $alert->item_id) }}" class="text-decoration-none">
                                                    {{ $alert->item->name }}
                                                </a>
                                            </td>
                                            <td>{{ $alert->item->category->name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($alert->scan_date)->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $alert->daily_scans }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('reports.item-scan-history', $alert->item_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View History
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Unscanned Items by Period -->
                <div class="row g-4 mb-4">
                    <!-- 30+ Days Unscanned -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock me-2 text-warning"></i>
                                    Items Not Scanned (30+ Days)
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($unscannedItems['30_days']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th>Current Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($unscannedItems['30_days']->take(10) as $item)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('reports.item-scan-history', $item->id) }}" class="text-decoration-none">
                                                                {{ $item->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                                        <td>{{ $item->current_stock }} {{ $item->unit }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($unscannedItems['30_days']->count() > 10)
                                            <p class="text-muted small mt-2">And {{ $unscannedItems['30_days']->count() - 10 }} more items...</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">All items have been scanned within the last 30 days.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 60+ Days Unscanned -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-times me-2 text-info"></i>
                                    Items Not Scanned (60+ Days)
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($unscannedItems['60_days']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th>Current Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($unscannedItems['60_days']->take(10) as $item)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('reports.item-scan-history', $item->id) }}" class="text-decoration-none">
                                                                {{ $item->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                                        <td>{{ $item->current_stock }} {{ $item->unit }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($unscannedItems['60_days']->count() > 10)
                                            <p class="text-muted small mt-2">And {{ $unscannedItems['60_days']->count() - 10 }} more items...</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">All items have been scanned within the last 60 days.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 90+ Days Unscanned -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-secondary"></i>
                            Items Not Scanned (90+ Days)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($unscannedItems['90_days']->count() > 0)
                            <div class="alert alert-warning">
                                <strong>Critical Alert:</strong> The following items have not been scanned for 90+ days. This may indicate they are not being properly monitored or maintained.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unscannedItems['90_days'] as $item)
                                            <tr>
                                                <td>
                                                    <strong>{{ $item->name }}</strong>
                                                </td>
                                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                                <td>{{ $item->current_stock }} {{ $item->unit }}</td>
                                                <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('reports.item-scan-history', $item->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-history me-1"></i>History
                                                    </a>
                                                    <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-eye me-1"></i>View Item
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-success">Excellent Monitoring Coverage</h5>
                                <p class="text-muted">All items have been scanned within the last 90 days, indicating good asset monitoring practices.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection