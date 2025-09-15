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

    /**
     * Export activity logs to CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject']);

        // Apply same filters as index
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

        $activities = $query->latest('created_at')->limit(5000)->get();

        $filename = 'activity_logs_' . Carbon::now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Log Type',
                'Event',
                'Description',
                'Subject Type',
                'Subject ID',
                'IP Address',
                'User Agent'
            ]);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->causer ? $activity->causer->name : 'System',
                    $activity->log_name,
                    $activity->event,
                    $activity->description,
                    $activity->subject_type,
                    $activity->subject_id,
                    $activity->ip_address,
                    $activity->user_agent
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get security report
     */
    public function securityReport(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(7)->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Failed login attempts
        $failedLogins = ActivityLog::where('event', 'login_failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        // Suspicious activities (multiple failed logins from same IP)
        $suspiciousIPs = $failedLogins->groupBy('ip_address')
            ->filter(function ($attempts) {
                return $attempts->count() >= 3;
            })
            ->map(function ($attempts) {
                return [
                    'ip' => $attempts->first()->ip_address,
                    'attempts' => $attempts->count(),
                    'latest_attempt' => $attempts->sortByDesc('created_at')->first()->created_at,
                    'user_agents' => $attempts->pluck('user_agent')->unique()
                ];
            });

        // Account lockouts
        $lockouts = ActivityLog::where('event', 'account_locked')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        // Administrative actions
        $adminActions = ActivityLog::whereIn('log_name', ['user_management', 'item_management'])
            ->whereIn('event', ['created', 'updated', 'deleted'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['causer'])
            ->latest()
            ->get();

        return view('admin.activity-logs.security', compact(
            'failedLogins',
            'suspiciousIPs', 
            'lockouts',
            'adminActions',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Get real-time activity feed
     */
    public function liveFeed(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        
        $activities = ActivityLog::with(['causer', 'subject'])
            ->where('id', '>', $lastId)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->formatted_description,
                    'user' => $activity->causer ? $activity->causer->name : 'System',
                    'time' => $activity->created_at->diffForHumans(),
                    'log_name' => $activity->log_name,
                    'event' => $activity->event,
                    'event_color' => $activity->getEventColorClass(),
                    'log_color' => $activity->getLogNameColorClass()
                ];
            }),
            'last_id' => $activities->max('id') ?? $lastId
        ]);
    }
}
