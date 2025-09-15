@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-semibold text-dark mb-1">
                        <i class="fas fa-calendar-times me-2 text-danger"></i>
                        Expiry Reports
                    </h2>
                    <p class="text-muted small mb-0">
                        Monitor product expiration dates and manage inventory freshness
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.expiry.export', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>All Reports
                    </a>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                            <h4 class="mb-1">{{ number_format($stats['total_items_with_expiry']) }}</h4>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h4 class="mb-1 text-danger">{{ number_format($stats['expired_items']) }}</h4>
                            <small class="text-muted">Expired</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <h4 class="mb-1 text-warning">{{ number_format($stats['expiring_this_week']) }}</h4>
                            <small class="text-muted">This Week</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <h4 class="mb-1 text-info">{{ number_format($stats['expiring_this_month']) }}</h4>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-secondary mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h4 class="mb-1">{{ number_format($stats['expiring_this_quarter']) }}</h4>
                            <small class="text-muted">This Quarter</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h4 class="mb-1 text-success">{{ number_format($stats['fresh_items']) }}</h4>
                            <small class="text-muted">Fresh</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Value at Risk Alert -->
            @if($stats['value_at_risk'] > 0)
                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-peso-sign fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">Value at Risk</h5>
                            <p class="mb-0">
                                <strong>₱{{ number_format($stats['value_at_risk'], 2) }}</strong> worth of inventory 
                                is expired or expiring within 30 days.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.expiry') }}" class="row g-3">
                        <!-- Category Filter -->
                        <div class="col-lg-3">
                            <label for="category_id" class="form-label small fw-medium">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-lg-3">
                            <label for="status" class="form-label small fw-medium">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Items</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                <option value="fresh" {{ request('status') == 'fresh' ? 'selected' : '' }}>Fresh</option>
                            </select>
                        </div>

                        <!-- Timeframe Filter -->
                        <div class="col-lg-3">
                            <label for="timeframe" class="form-label small fw-medium">Timeframe (Days)</label>
                            <select name="timeframe" id="timeframe" class="form-select">
                                <option value="7" {{ request('timeframe') == '7' ? 'selected' : '' }}>Next 7 days</option>
                                <option value="30" {{ request('timeframe', '30') == '30' ? 'selected' : '' }}>Next 30 days</option>
                                <option value="60" {{ request('timeframe') == '60' ? 'selected' : '' }}>Next 60 days</option>
                                <option value="90" {{ request('timeframe') == '90' ? 'selected' : '' }}>Next 90 days</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-lg-3">
                            <label class="form-label small fw-medium">&nbsp;</label>
                            <div class="d-grid gap-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('reports.expiry') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expiry Chart -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Expiry Trends (Next 12 Months)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="expiryChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Expiry Details
                            <span class="badge bg-primary ms-2">{{ $items->count() }} items</span>
                        </h5>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Details</th>
                                        <th>Category</th>
                                        <th>Stock & Value</th>
                                        <th>Expiry Status</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr class="{{ $item['is_expired'] ? 'table-danger' : ($item['status'] == 'critical' ? 'table-warning' : '') }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        @php
                                                            $iconClass = match($item['status']) {
                                                                'expired' => 'fas fa-times-circle text-danger',
                                                                'critical' => 'fas fa-exclamation-triangle text-warning',
                                                                'warning' => 'fas fa-exclamation-circle text-warning',
                                                                'caution' => 'fas fa-clock text-info',
                                                                default => 'fas fa-check-circle text-success'
                                                            };
                                                        @endphp
                                                        <i class="{{ $iconClass }} fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">
                                                            <a href="{{ route('items.show', $item['id']) }}" 
                                                               class="text-decoration-none text-dark">
                                                                {{ $item['name'] }}
                                                            </a>
                                                        </h6>
                                                        @if($item['brand'])
                                                            <small class="text-muted">{{ $item['brand'] }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $item['category'] }}</span>
                                            </td>
                                            
                                            <td>
                                                <div>
                                                    <strong>{{ number_format($item['current_stock']) }} units</strong>
                                                    @if($item['unit_price'])
                                                        <small class="text-muted d-block">
                                                            ₱{{ number_format($item['total_value'], 2) }} total value
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div>
                                                    <span class="badge {{ match($item['status']) {
                                                        'expired' => 'bg-danger',
                                                        'critical' => 'bg-warning text-dark',
                                                        'warning' => 'bg-warning text-dark',
                                                        'caution' => 'bg-info',
                                                        default => 'bg-success'
                                                    } }}">
                                                        {{ ucfirst($item['status']) }}
                                                    </span>
                                                    <div class="small text-muted mt-1">
                                                        <strong>{{ $item['expiry_date']->format('M j, Y') }}</strong>
                                                    </div>
                                                    <div class="small">
                                                        @if($item['is_expired'])
                                                            <span class="text-danger">
                                                                Expired {{ abs($item['days_until_expiry']) }} days ago
                                                            </span>
                                                        @else
                                                            <span class="text-muted">
                                                                {{ $item['days_until_expiry'] }} days remaining
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                {{ $item['location'] }}
                                            </td>
                                            
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('items.show', $item['id']) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('items.edit', $item['id']) }}" 
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($item['is_expired'] || $item['status'] == 'critical')
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                onclick="handleExpiredItem({{ $item['id'] }}, '{{ $item['name'] }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No expiry data found</h5>
                            <p class="text-muted">No items match your current filter criteria.</p>
                            <a href="{{ route('reports.expiry') }}" class="btn btn-primary">
                                <i class="fas fa-refresh me-1"></i>View All Items
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Expiry Chart
    const ctx = document.getElementById('expiryChart').getContext('2d');
    const monthlyData = @json($stats['monthly_expiry']);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Items Expiring',
                data: monthlyData.map(item => item.count),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});

function handleExpiredItem(itemId, itemName) {
    if (confirm(`Are you sure you want to mark "${itemName}" for disposal? This action cannot be undone.`)) {
        // Here you would typically make an AJAX call to handle the expired item
        alert('Feature coming soon: Expired item handling workflow');
    }
}
</script>
@endsection
