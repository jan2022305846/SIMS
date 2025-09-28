<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Scan Analytics Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .stats { display: table; width: 100%; margin-bottom: 30px; }
        .stat-box { display: table-cell; width: 25%; text-align: center; padding: 15px; border: 1px solid #ddd; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .charts { margin: 30px 0; }
        .chart-placeholder { border: 1px solid #ddd; padding: 40px; text-align: center; color: #666; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .section-title { font-size: 18px; font-weight: bold; margin: 30px 0 15px 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
        .date-range { text-align: center; margin-bottom: 20px; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>QR Code Scan Analytics Report</h1>
        <div class="date-range">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </div>
        <p>Generated on: {{ date('M d, Y H:i') }}</p>
    </div>

    <!-- Statistics Overview -->
    <div class="section-title">Overview Statistics</div>
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['total_scans']) }}</div>
            <div class="stat-label">Total Scans</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['unique_items_scanned']) }}</div>
            <div class="stat-label">Items Scanned</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['unique_users_scanning']) }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['unscanned_items']) }}</div>
            <div class="stat-label">Unscanned (30+ days)</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="section-title">Scan Activity Trends</div>
    <div class="charts">
        <div class="chart-placeholder">
            [Daily Scan Activity Chart - Interactive chart available in web version]
        </div>
        <div class="chart-placeholder">
            [Scanner Type Distribution Chart - Interactive chart available in web version]
        </div>
    </div>

    <!-- Most Scanned Items -->
    <div class="section-title">Most Scanned Items</div>
    @if($analytics['most_scanned_items']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Scan Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analytics['most_scanned_items'] as $item)
                    <tr>
                        <td>{{ $item['item']->name }}</td>
                        <td>{{ $item['item']->category->name ?? 'N/A' }}</td>
                        <td>{{ $item['scan_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No scan data available for the selected period.</p>
    @endif

    <!-- Scans by Location -->
    <div class="section-title">Scans by Location</div>
    @if($analytics['scans_by_location']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Scan Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analytics['scans_by_location'] as $location => $count)
                    <tr>
                        <td>{{ $location ?: 'Unknown' }}</td>
                        <td>{{ $count }}</td>
                        <td>{{ number_format(($count / $analytics['total_scans']) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No location data available.</p>
    @endif

    <!-- Recent Scan Activity -->
    <div class="section-title">Recent Scan Activity</div>
    @if($scanLogs->count() > 0)
        <table>
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
                @foreach($scanLogs as $log)
                    <tr>
                        <td>{{ $log->scanned_at->format('M d, Y H:i') }}</td>
                        <td>{{ $log->item->name }}</td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td>{{ $log->location ?: 'N/A' }}</td>
                        <td>{{ ucfirst($log->scanner_type) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No scan activity found for the selected period.</p>
    @endif

    <div class="footer">
        <p>This report was generated by the Supply Office Management System</p>
        <p>Confidential - For internal use only</p>
    </div>
</body>
</html>