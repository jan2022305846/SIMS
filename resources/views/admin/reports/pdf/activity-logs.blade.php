<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs Report - SIMS</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #1a1851;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .filters {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #1a1851;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            font-size: 8px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-primary { background-color: #007bff; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-dark { background-color: #343a40; color: white; }
        .badge-light { background-color: #f8f9fa; color: black; }
        .text-muted {
            color: #6c757d;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Activity Logs Report</h1>
        <p>USTP Panaon Supply Office Inventory Management System</p>
        <p><strong>Generated on:</strong> {{ date('F d, Y H:i:s') }}</p>
    </div>

    @if($request->hasAny(['log_name', 'user_id', 'date_from', 'date_to']) && ($request->log_name || $request->user_id || $request->date_from || $request->date_to))
    <div class="filters">
        <strong>Applied Filters:</strong><br>
        @if($request->log_name && $request->log_name !== 'all')
            <strong>Activity Type:</strong> {{ ucfirst(str_replace('_', ' ', $request->log_name)) }}<br>
        @endif
        @if($request->user_id && $request->user_id !== 'all')
            <strong>User:</strong> {{ \App\Models\User::find($request->user_id)?->name ?? 'Unknown' }}<br>
        @endif
        @if($request->date_from)
            <strong>From Date:</strong> {{ $request->date_from }}<br>
        @endif
        @if($request->date_to)
            <strong>To Date:</strong> {{ $request->date_to }}<br>
        @endif
    </div>
    @endif

    @if($logs->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 12%;">Timestamp</th>
                    <th style="width: 15%;">User</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%;">Subject</th>
                    <th style="width: 8%;">IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>
                        {{ $log->created_at->format('M d, Y') }}<br>
                        <small>{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        @if($log->causer)
                            <strong>{{ $log->causer->name }}</strong><br>
                            <small>{{ $log->causer->username }}</small>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $log->log_name == 'auth' ? 'primary' : ($log->log_name == 'request' ? 'info' : ($log->log_name == 'item' ? 'success' : ($log->log_name == 'user_management' ? 'warning' : 'secondary'))) }}">
                            {{ ucfirst(str_replace('_', ' ', $log->log_name)) }}
                        </span>
                    </td>
                    <td>
                        {{ $log->description }}
                        @if($log->properties && isset($log->properties['reason']))
                            <br><small><strong>Reason:</strong> {{ $log->properties['reason'] }}</small>
                        @endif
                        @if($log->properties && isset($log->properties['changes']))
                            <br><small><strong>Changes:</strong> {{ json_encode($log->properties['changes']) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($log->subject)
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            @if(method_exists($log->subject, 'name'))
                                <br><small>{{ Str::limit($log->subject->name, 20) }}</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ $log->ip_address ?: 'N/A' }}</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Total Records:</strong> {{ $logs->count() }}</p>
            <p>This report was generated by SIMS on {{ date('F d, Y \a\t H:i:s') }}</p>
        </div>
    @else
        <div class="no-data">
            <h3>No Activity Logs Found</h3>
            <p>No activity logs match the specified criteria.</p>
        </div>
    @endif
</body>
</html>