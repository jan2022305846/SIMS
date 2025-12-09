<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;

class LogActivityStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:stats {--days=7 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display activity log statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info('📊 Activity Log Statistics');
        $this->info('===========================');

        // Total logs
        $totalLogs = ActivityLog::count();
        $this->info("Total activity logs: {$totalLogs}");

        // Logs in the last N days
        $recentLogs = ActivityLog::where('created_at', '>=', now()->subDays($days))->count();
        $this->info("Logs in last {$days} days: {$recentLogs}");

        // Logs by type
        $this->newLine();
        $this->info('📋 Activity Breakdown by Type:');
        $logTypes = ActivityLog::select('log_name', DB::raw('count(*) as count'))
            ->groupBy('log_name')
            ->orderBy('count', 'desc')
            ->get();

        foreach ($logTypes as $type) {
            $this->info("  {$type->log_name}: {$type->count}");
        }

        // Most active users (causers)
        $this->newLine();
        $this->info('👥 Most Active Users:');
        $activeUsers = ActivityLog::select('causer_type', 'causer_id', DB::raw('count(*) as count'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_type', 'causer_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->with('causer')
            ->get();

        foreach ($activeUsers as $userActivity) {
            $causer = $userActivity->causer;
            $name = $causer ? $causer->name : 'Unknown';
            $this->info("  {$name}: {$userActivity->count} activities");
        }

        // Recent activities
        $this->newLine();
        $this->info('🕒 Recent Activities:');
        $recentActivities = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentActivities as $activity) {
            $causer = $activity->causer ? $activity->causer->name : 'System';
            $time = $activity->created_at->diffForHumans();
            $this->info("  [{$time}] {$causer}: {$activity->description}");
        }

        $this->newLine();
        $this->info('✅ Activity logging is active and tracking system activities!');
    }
}
