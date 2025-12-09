<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log user authentication activities
     */
    public static function logAuth(string $action, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "User {$action}",
            null,
            $user,
            'auth',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Log user login
     */
    public static function logLogin(?Model $user = null): ActivityLog
    {
        return self::logAuth('logged in', $user);
    }

    /**
     * Log user logout
     */
    public static function logLogout(?Model $user = null): ActivityLog
    {
        return self::logAuth('logged out', $user);
    }

    /**
     * Log user failed login attempt
     */
    public static function logFailedLogin(array $credentials = []): ActivityLog
    {
        $properties = ['username' => $credentials['username'] ?? null];
        return ActivityLog::log(
            'Failed login attempt',
            null,
            null,
            'auth',
            $properties
        );
    }

    /**
     * Log request-related activities
     */
    public static function logRequest(string $action, Model $request, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "Request {$action}",
            $request,
            $user,
            'request',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Log request creation
     */
    public static function logRequestCreated(Model $request, ?Model $user = null): ActivityLog
    {
        return self::logRequest('created', $request, $user, [
            'item_count' => $request->requestItems()->count()
        ]);
    }

    /**
     * Log request approval
     */
    public static function logRequestApproved(Model $request, ?Model $user = null): ActivityLog
    {
        return self::logRequest('approved', $request, $user);
    }

    /**
     * Log request decline
     */
    public static function logRequestDeclined(Model $request, ?Model $user = null, ?string $reason = null): ActivityLog
    {
        return self::logRequest('declined', $request, $user, [
            'reason' => $reason
        ]);
    }

    /**
     * Log request cancellation
     */
    public static function logRequestCancelled(Model $request, ?Model $user = null, ?string $reason = null): ActivityLog
    {
        return self::logRequest('cancelled', $request, $user, [
            'reason' => $reason
        ]);
    }

    /**
     * Log item-related activities
     */
    public static function logItem(string $action, Model $item, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "Item {$action}",
            $item,
            $user,
            'item',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Log item creation
     */
    public static function logItemCreated(Model $item, ?Model $user = null): ActivityLog
    {
        return self::logItem('created', $item, $user, [
            'type' => class_basename($item),
            'name' => $item->name,
            'quantity' => $item->quantity ?? 0
        ]);
    }

    /**
     * Log item update
     */
    public static function logItemUpdated(Model $item, ?Model $user = null, array $changes = []): ActivityLog
    {
        return self::logItem('updated', $item, $user, [
            'type' => class_basename($item),
            'name' => $item->name,
            'changes' => $changes
        ]);
    }

    /**
     * Log item deletion
     */
    public static function logItemDeleted(Model $item, ?Model $user = null): ActivityLog
    {
        return self::logItem('deleted', $item, $user, [
            'type' => class_basename($item),
            'name' => $item->name
        ]);
    }

    /**
     * Log stock-related activities
     */
    public static function logStock(string $action, Model $item, int $quantity, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "Stock {$action}",
            $item,
            $user,
            'stock',
            array_merge($properties, [
                'action' => $action,
                'quantity' => $quantity,
                'type' => class_basename($item),
                'name' => $item->name
            ])
        );
    }

    /**
     * Log stock addition
     */
    public static function logStockAdded(Model $item, int $quantity, ?Model $user = null): ActivityLog
    {
        return self::logStock('added', $item, $quantity, $user);
    }

    /**
     * Log stock reduction
     */
    public static function logStockReduced(Model $item, int $quantity, ?Model $user = null, ?string $reason = null): ActivityLog
    {
        return self::logStock('reduced', $item, $quantity, $user, [
            'reason' => $reason
        ]);
    }

    /**
     * Log user management activities
     */
    public static function logUser(string $action, Model $targetUser, ?Model $admin = null, array $properties = []): ActivityLog
    {
        $admin = $admin ?: Auth::user();
        return ActivityLog::log(
            "User {$action}",
            $targetUser,
            $admin,
            'user_management',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Log user creation
     */
    public static function logUserCreated(Model $user, ?Model $admin = null): ActivityLog
    {
        return self::logUser('created', $user, $admin, [
            'role' => $user->role,
            'office' => $user->office->name ?? null
        ]);
    }

    /**
     * Log user update
     */
    public static function logUserUpdated(Model $user, ?Model $admin = null, array $changes = []): ActivityLog
    {
        return self::logUser('updated', $user, $admin, [
            'changes' => $changes
        ]);
    }

    /**
     * Log user deletion
     */
    public static function logUserDeleted(Model $user, ?Model $admin = null): ActivityLog
    {
        return self::logUser('deleted', $user, $admin);
    }

    /**
     * Log QR scan activities
     */
    public static function logQrScan(Model $item, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            'QR code scanned',
            $item,
            $user,
            'qr_scan',
            array_merge($properties, [
                'type' => class_basename($item),
                'name' => $item->name
            ])
        );
    }

    /**
     * Log report generation
     */
    public static function logReportGenerated(string $reportType, ?Model $user = null, array $filters = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "Report generated: {$reportType}",
            null,
            $user,
            'report',
            [
                'report_type' => $reportType,
                'filters' => $filters
            ]
        );
    }

    /**
     * Log system activities
     */
    public static function logSystem(string $action, array $properties = []): ActivityLog
    {
        return ActivityLog::log(
            "System: {$action}",
            null,
            null,
            'system',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Log notification activities
     */
    public static function logNotification(string $action, Model $notification, ?Model $user = null, array $properties = []): ActivityLog
    {
        $user = $user ?: Auth::user();
        return ActivityLog::log(
            "Notification {$action}",
            $notification,
            $user,
            'notification',
            array_merge($properties, ['action' => $action])
        );
    }

    /**
     * Get recent activities
     */
    public static function getRecentActivities(int $limit = 50, ?string $logName = null)
    {
        $query = ActivityLog::with(['causer', 'subject'])
                           ->orderBy('created_at', 'desc')
                           ->limit($limit);

        if ($logName) {
            $query->where('log_name', $logName);
        }

        return $query->get();
    }

    /**
     * Get activities for a specific user
     */
    public static function getUserActivities(Model $user, int $limit = 50)
    {
        return ActivityLog::byCauser($user)
                         ->with(['subject'])
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }
}