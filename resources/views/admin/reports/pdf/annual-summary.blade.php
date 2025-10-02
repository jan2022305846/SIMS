<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Annual Summary Report - {{$startDate->year}}</title>
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
        .date-range { text-align: center; margin-bottom: 20px; font-style: italic; }
        .item-table { margin: 20px 0; }
        .item-row { border: 1px solid #ddd; margin-bottom: 10px; }
        .item-header { background-color: #f8f9fa; padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd; }
        .item-details { display: table; width: 100%; }
        .detail-row { display: table-row; }
        .detail-cell { display: table-cell; padding: 8px; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; width: 200px; background-color: #f8f9fa; }
        .detail-value { }
    </style>
</head>
<body>
    <div class="header">
        <h1>Annual Summary Report</h1>
        <div class="date-range">
            Period: {{$startDate->year}} ({{$startDate->format('M d, Y')}} - {{$endDate->format('M d, Y')}})
        </div>
        <p>Generated on: {{ date('M d, Y H:i') }}</p>
    </div>

    <!-- Statistics Overview -->
    <div class="section-title">Overview Statistics</div>
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['total_requests']) }}</div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($analytics['approved_requests']) }}</div>
            <div class="stat-label">Approved Requests</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($annualData['low_stock_items']->count()) }}</div>
            <div class="stat-label">Low Stock Items</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($annualData['inventory_changes']->count()) }}</div>
            <div class="stat-label">Items Updated</div>
        </div>
    </div>

    <!-- Item Details Section -->
    <div class="section-title">Item Movement Summary</div>
    <div style="font-size: 11px; color: #666; margin-bottom: 15px; font-style: italic;">
        <strong>Note:</strong> Stock additions during the reporting period are tracked and accounted for in beginning stock calculations.
    </div>
    @php
        // Group requests by item and calculate quantities
        $itemSummary = [];
        foreach ($annualData['requests'] as $request) {
            $itemId = $request->item->id;
            if (!isset($itemSummary[$itemId])) {
                $itemSummary[$itemId] = [
                    'item' => $request->item,
                    'total_requests' => 0, // Total requests made for the item
                    'claimed_released_quantity' => 0, // Items actually disbursed/claimed
                    'current_stock' => $request->item->current_stock ?? 0, // Current remaining stock
                    'stock_additions' => 0, // Stock added during the year
                ];
            }
            $itemSummary[$itemId]['total_requests'] += $request->quantity;

            // Count as claimed/released if status is fulfilled or claimed
            if (in_array($request->status, ['fulfilled', 'claimed'])) {
                $itemSummary[$itemId]['claimed_released_quantity'] += $request->quantity;
            }
        }

        // Calculate stock additions during the year from ActivityLog
        use App\Models\ActivityLog;
        $stockAdditionLogs = ActivityLog::where('log_name', 'item_management')
            ->where('event', 'updated')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('properties', function($query) {
                $query->whereJsonContains('changes->quantity->to', 'exists'); // Check if quantity was changed
            })
            ->with('subject')
            ->get();

        foreach ($stockAdditionLogs as $log) {
            $changes = $log->getExtraProperty('changes', collect());
            if (isset($changes['quantity'])) {
                $oldQuantity = $changes['quantity']['from'] ?? 0;
                $newQuantity = $changes['quantity']['to'] ?? 0;
                $addition = $newQuantity - $oldQuantity;

                if ($addition > 0 && $log->subject) {
                    $itemId = $log->subject->id;
                    if (isset($itemSummary[$itemId])) {
                        $itemSummary[$itemId]['stock_additions'] += $addition;
                    }
                }
            }
        }

        // Calculate beginning stock for each item: current stock + claimed quantity - stock additions
        foreach ($itemSummary as &$summary) {
            $summary['beginning_stock'] = $summary['current_stock'] + $summary['claimed_released_quantity'] - $summary['stock_additions'];
        }
    @endphp

    @if(count($itemSummary) > 0)
        @foreach($itemSummary as $summary)
            <div class="item-row">
                <div class="item-header">
                    {{ $summary['item']->name }}
                    <span style="font-weight: normal; font-size: 12px; color: #666;">
                        ({{ $summary['item']->category->name ?? 'No Category' }})
                    </span>
                </div>
                <div class="item-details">
                    <div class="detail-row">
                        <div class="detail-cell detail-label">Item Name:</div>
                        <div class="detail-cell detail-value">{{ $summary['item']->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-cell detail-label">Claimed/Released Quantity:</div>
                        <div class="detail-cell detail-value">{{ number_format($summary['claimed_released_quantity']) }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-cell detail-label">Stock Additions (During Period):</div>
                        <div class="detail-cell detail-value">{{ number_format($summary['stock_additions']) }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-cell detail-label">Annual Stock (Beginning):</div>
                        <div class="detail-cell detail-value">{{ number_format($summary['beginning_stock']) }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-cell detail-label">Remaining Stock:</div>
                        <div class="detail-cell detail-value">{{ number_format($summary['current_stock']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <p>No item movement data available for the selected year.</p>
    @endif

    <!-- Quarterly Breakdown -->
    <div class="section-title">Quarterly Request Breakdown</div>
    @if(count($analytics['quarterly_breakdown']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Quarter</th>
                    <th>Requests</th>
                    <th>Disbursements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analytics['quarterly_breakdown'] as $quarter => $data)
                    <tr>
                        <td>{{ $quarter }}</td>
                        <td>{{ $data['requests'] }}</td>
                        <td>{{ $data['disbursements'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No quarterly breakdown data available.</p>
    @endif

    <!-- Department Performance -->
    <div class="section-title">Department Performance</div>
    @if(count($analytics['department_performance']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Requests</th>
                    <th>Approved</th>
                    <th>Pending</th>
                    <th>Approval Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analytics['department_performance'] as $dept => $performance)
                    <tr>
                        <td>{{ $dept ?: 'Unspecified' }}</td>
                        <td>{{ $performance['total'] }}</td>
                        <td>{{ $performance['approved'] }}</td>
                        <td>{{ $performance['pending'] }}</td>
                        <td>{{ number_format($performance['approval_rate'], 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No department performance data available.</p>
    @endif

    <!-- Low Stock Items -->
    <div class="section-title">Low Stock Alert Items</div>
    @if($annualData['low_stock_items']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($annualData['low_stock_items'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                        <td>{{ number_format($item->current_stock ?? 0) }}</td>
                        <td>{{ number_format($item->minimum_stock ?? 0) }}</td>
                        <td style="color: red; font-weight: bold;">LOW STOCK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No items are currently low on stock.</p>
    @endif

    <div class="footer">
        <p>This report was generated by the Supply Office Management System</p>
        <p>Confidential - For internal use only</p>
    </div>
</body>
</html>