<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - {{ ucfirst($data['period']) }} {{ $data['selection'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 28px;
            color: #007bff;
        }
        .summary-card p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        .chart-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }
        .chart-section h2 {
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .period-info {
            background: #e9ecef;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supply Office Inventory Report</h1>
        <p><strong>Period:</strong> {{ ucfirst($data['period']) }} - {{ $data['selection'] }}</p>
        <p><strong>Report Date:</strong> {{ date('F d, Y') }}</p>
        <p><strong>Date Range:</strong> {{ $data['dateFrom'] }} to {{ $data['dateTo'] }}</p>
    </div>

    <div class="summary">
        <div class="summary-card">
            <h3>{{ number_format($data['summary']['totalItems']) }}</h3>
            <p>Total Items</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($data['summary']['totalAdded']) }}</h3>
            <p>Items Added/Restocked</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($data['summary']['totalReleased']) }}</h3>
            <p>Items Released/Claimed</p>
        </div>
        <div class="summary-card">
            <h3>{{ number_format($data['summary']['currentStock']) }}</h3>
            <p>Current Stock</p>
        </div>
    </div>

    <div class="chart-section">
        <h2>Inventory Movement Chart</h2>
        <p><em>Chart visualization would be included here in a graphical PDF</em></p>
        <table class="table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Items Added</th>
                    <th>Items Released</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['chartData'] as $point)
                <tr>
                    <td>{{ $point['date'] }}</td>
                    <td>{{ $point['added'] }}</td>
                    <td>{{ $point['released'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="chart-section">
        <h2>Detailed Inventory Report</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Items Released/Claimed</th>
                    <th>Total Quantity (Start + Added)</th>
                    <th>Remaining Stock</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['tableData'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['released'] }}</td>
                    <td>{{ $item['totalQuantity'] }}</td>
                    <td>{{ $item['remaining'] }}</td>
                    <td>{{ $item['category'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Generated by Supply Office Management System on {{ date('F d, Y \a\t H:i:s') }}</p>
        <p>This report contains confidential information. Please handle accordingly.</p>
    </div>
</body>
</html>