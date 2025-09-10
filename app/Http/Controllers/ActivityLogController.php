<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->latest('created_at');

        // Apply filters
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->paginate(25);

        // Get filter data
        $logNames = ActivityLog::distinct('log_name')
            ->whereNotNull('log_name')
            ->pluck('log_name');

        $events = ActivityLog::distinct('event')
            ->whereNotNull('event')
            ->pluck('event');

        $users = User::select('id', 'name')->get();

        return view('admin.activity-logs.index', compact(
            'activities', 
            'logNames', 
            'events', 
            'users'
        ));
    }

    public function show(ActivityLog $activityLog): View
    {
        $activityLog->load(['causer', 'subject']);
        
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function userActivity(Request $request, User $user): View
    {
        $activities = ActivityLog::where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->with(['subject'])
            ->latest('created_at')
            ->paginate(25);

        return view('admin.activity-logs.user-activity', compact('activities', 'user'));
    }

    public function analytics(Request $request): View
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Activity statistics
        $stats = [
            'total_activities' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'unique_users' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->distinct('causer_id')
                ->count(),
            'activities_today' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
            'activities_this_week' => ActivityLog::whereBetween('created_at', [
                Carbon::now()->startOfWeek(), 
                Carbon::now()->endOfWeek()
            ])->count(),
        ];

        // Activity by log type
        $activityByLogType = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('log_name, COUNT(*) as count')
            ->groupBy('log_name')
            ->get();

        // Activity by event type
        $activityByEvent = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('event, COUNT(*) as count')
            ->whereNotNull('event')
            ->groupBy('event')
            ->get();

        // Top active users
        $topUsers = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('causer')
            ->selectRaw('causer_id, COUNT(*) as activity_count')
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get();

        // Daily activity trend
        $dailyActivity = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent critical activities
        $criticalActivities = ActivityLog::whereIn('event', ['created', 'deleted', 'approved', 'declined'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['causer', 'subject'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.activity-logs.analytics', compact(
            'stats',
            'activityByLogType',
            'activityByEvent',
            'topUsers',
            'dailyActivity',
            'criticalActivities',
            'dateFrom',
            'dateTo'
        ));
    }

    public function cleanup(Request $request)
    {
        $request->validate([
            'older_than_days' => 'required|integer|min:7|max:365'
        ]);

        $olderThanDays = $request->older_than_days;
        $cutoffDate = Carbon::now()->subDays($olderThanDays);

        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        return back()->with('success', "Successfully deleted {$deletedCount} activity log entries older than {$olderThanDays} days.");
    }
}
