@extends('layouts.app')

@section('title', 'Activity Logs Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Activity Logs Report
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('reports.activity-logs', array_filter(array_merge(request()->query(), ['format' => 'docx']))) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export as DOCX
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('reports.activity-logs') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="log_name">Activity Type</label>
                                    <select name="log_name" id="log_name" class="form-control">
                                        <option value="all" {{ $logName == 'all' || !$logName ? 'selected' : '' }}>All Types</option>
                                        @foreach($logNames as $name)
                                            <option value="{{ $name }}" {{ $logName == $name ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id">User</label>
                                    <select name="user_id" id="user_id" class="form-control">
                                        <option value="all" {{ $userId == 'all' || !$userId ? 'selected' : '' }}>All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_from">From Date</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control"
                                           value="{{ $dateFrom }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_to">To Date</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control"
                                           value="{{ $dateTo }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('reports.activity-logs') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Activity Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Activity Type</th>
                                    <th>Description</th>
                                    <th>Subject</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $log->created_at->format('M d, Y') }}<br>
                                                {{ $log->created_at->format('H:i:s') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($log->causer)
                                                <strong>{{ $log->causer->name }}</strong><br>
                                                <small class="text-muted">{{ $log->causer->username }}</small>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $badgeClasses[$log->log_name] ?? $badgeClasses['default'] }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->log_name)) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $log->description }}
                                            @if($log->properties && isset($log->properties['reason']))
                                                <br><small class="text-muted">Reason: {{ $log->properties['reason'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->subject)
                                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                                @if(method_exists($log->subject, 'name'))
                                                    <br><small>{{ $log->subject->name }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $log->ip_address ?: 'N/A' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No activity logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($activityLogs->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $activityLogs->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection