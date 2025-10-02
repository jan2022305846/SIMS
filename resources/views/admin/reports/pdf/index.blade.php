<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $data['period'] }} Dashboard Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .stats { display: table; width: 100%; margin-bottom: 30px; }
        .stat-box { display: table-cell; width: 25%; text-align: center; padding: 15px; border: 1px solid #ddd; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .section-title { font-size: 18px; font-weight: bold; margin: 30px 0 15px 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
        .chart-placeholder { text-align: center; padding: 40px; background-color: #f8f9fa; border: 1px solid #dee2e6; margin: 20px 0; }
        .chart-placeholder p { margin: 0; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $data['period'] }} Dashboard Report</h1>
        <div class="date-range">
            Period: {{ $data['current_date'] }}
        </div>
        <p>Generated on: {{ date('M d, Y H:i') }}</p>
    </div>

    <!-- Statistics Overview -->
    <div class="section-title">Overview Statistics</div>
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number">{{ number_format($data['summary']['total_requests']) }}</div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($data['summary']['fulfilled_requests']) }}</div>
            <div class="stat-label">Fulfilled Requests</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($data['summary']['pending_requests']) }}</div>
            <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($data['unique_users']) }}</div>
            <div class="stat-label">Unique Users</div>
        </div>
    </div>

    <!-- Chart Data Summary -->
    <div class="section-title">{{ $data['period'] }} Trend</div>
    <div class="chart-placeholder">
        <p>Chart visualization would be displayed here in the web interface</p>
        <p>Data points: {{ count($data['chart_data']) }} periods</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Requests</th>
                <th>Fulfilled</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['chart_data'] as $point)
                <tr>
                    <td>{{ $point['date'] }}</td>
                    <td>{{ number_format($point['requests']) }}</td>
                    <td>{{ number_format($point['disbursements']) }}</td>
                    <td>${{ number_format($point['value'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Recent Records -->
    <div class="section-title">Recent {{ $data['period'] }} Records</div>
    @if(count($data['records']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['records']->take(50) as $record)
                    <tr>
                        <td>{{ $record->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $record->user->name ?? 'N/A' }}</td>
                        <td>{{ $record->item->name ?? 'N/A' }}</td>
                        <td>{{ number_format($record->quantity) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</td>
                        <td>{{ $record->department ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($data['records']) > 50)
            <p><em>Showing first 50 records. Total records: {{ count($data['records']) }}</em></p>
        @endif
    @else
        <p>No records found for the selected period.</p>
    @endif

    <!-- Summary Information -->
    <div class="section-title">Summary</div>
    <table>
        <tbody>
            <tr>
                <td style="font-weight: bold; width: 200px;">Report Period:</td>
                <td>{{ $data['period'] }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Date Range:</td>
                <td>{{ $data['current_date'] }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Requests:</td>
                <td>{{ number_format($data['summary']['total_requests']) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Fulfilled Requests:</td>
                <td>{{ number_format($data['summary']['fulfilled_requests']) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Pending Requests:</td>
                <td>{{ number_format($data['summary']['pending_requests']) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Value:</td>
                <td>${{ number_format($data['summary']['total_value'], 2) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Unique Users:</td>
                <td>{{ number_format($data['unique_users']) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated by the Supply Office Management System</p>
        <p>Confidential - For internal use only</p>
    </div>
</body>
</html>