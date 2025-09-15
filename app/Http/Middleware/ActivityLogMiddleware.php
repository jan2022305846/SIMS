<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogMiddleware
{
    /**
     * Handle an incoming request and log user activities
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and specific routes
        if (Auth::check() && $this->shouldLog($request)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Determine if this request should be logged
     */
    private function shouldLog(Request $request): bool
    {
        // Skip API routes and asset requests
        if ($request->is('api/*') || $request->is('_debugbar/*') || $request->is('build/*')) {
            return false;
        }

        // Skip GET requests to certain routes
        if ($request->isMethod('GET') && $this->isViewOnlyRoute($request)) {
            return false;
        }

        // Log all POST, PUT, DELETE, PATCH requests
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }

        // Log specific GET requests (login, logout, etc.)
        return $this->isImportantGetRoute($request);
    }

    /**
     * Check if it's a view-only route that doesn't need logging
     */
    private function isViewOnlyRoute(Request $request): bool
    {
        $viewOnlyPatterns = [
            'dashboard',
            '*/create',
            '*/edit',
            '*/show',
            'reports/*',
            'browse-items',
            'my-requests'
        ];

        foreach ($viewOnlyPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if it's an important GET route that should be logged
     */
    private function isImportantGetRoute(Request $request): bool
    {
        $importantPatterns = [
            'login',
            'logout',
            'qr/scanner',
            '*/download',
            '*/export'
        ];

        foreach ($importantPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the activity
     */
    private function logActivity(Request $request, $response): void
    {
        $user = Auth::user();
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $action = $this->determineAction($request, $routeName);
        $description = $this->generateDescription($request, $action, $user);

        // Prepare activity data
        $properties = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $routeName,
            'action' => $action,
            'input' => $this->sanitizeInput($request->all()),
            'response_status' => $response->getStatusCode(),
            'session_id' => $request->session()->getId(),
            'timestamp' => Carbon::now()->toISOString()
        ];

        // Add user agent and IP
        $properties['user_agent'] = $request->userAgent();
        $properties['ip_address'] = $request->ip();

        // Log the activity
        $activityLog = ActivityLog::log($description)
            ->inLog($this->getLogCategory($request))
            ->withProperties($properties)
            ->withEvent($action);
            
        // Only set causedBy if user is authenticated and is a Model instance
        if ($user instanceof \Illuminate\Database\Eloquent\Model) {
            $activityLog->causedBy($user);
        }
        
        $activityLog->save();
    }

    /**
     * Determine the action being performed
     */
    private function determineAction(Request $request, ?string $routeName): string
    {
        // Map HTTP methods to actions
        $methodActions = [
            'POST' => 'created',
            'PUT' => 'updated',
            'PATCH' => 'updated',
            'DELETE' => 'deleted'
        ];

        if (isset($methodActions[$request->method()])) {
            return $methodActions[$request->method()];
        }

        // Special cases for GET requests
        if ($request->isMethod('GET')) {
            if ($request->is('login')) return 'login_page_accessed';
            if ($request->is('logout')) return 'logged_out';
            if ($request->is('*/download')) return 'downloaded';
            if ($request->is('*/export')) return 'exported';
            if ($request->is('qr/*')) return 'qr_scanned';
        }

        return 'accessed';
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(Request $request, string $action, $user): string
    {
        $userName = $user->name;
        $resource = $this->getResourceFromRoute($request);

        switch ($action) {
            case 'created':
                return "{$userName} created a new {$resource}";
            case 'updated':
                return "{$userName} updated {$resource}";
            case 'deleted':
                return "{$userName} deleted {$resource}";
            case 'logged_out':
                return "{$userName} logged out of the system";
            case 'downloaded':
                return "{$userName} downloaded {$resource}";
            case 'exported':
                return "{$userName} exported {$resource} data";
            case 'qr_scanned':
                return "{$userName} scanned a QR code";
            case 'login_page_accessed':
                return "{$userName} accessed the login page";
            default:
                return "{$userName} {$action} {$resource}";
        }
    }

    /**
     * Extract resource type from route
     */
    private function getResourceFromRoute(Request $request): string
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $uri = $request->path();

        // Extract resource from route name
        if ($routeName) {
            $parts = explode('.', $routeName);
            if (count($parts) > 1) {
                return $parts[0]; // e.g., 'users.store' -> 'users'
            }
        }

        // Extract from URI
        $segments = explode('/', $uri);
        $resource = $segments[0] ?? 'resource';

        // Map common resources
        $resourceMap = [
            'items' => 'item',
            'users' => 'user',
            'categories' => 'category',
            'requests' => 'request',
            'reports' => 'report'
        ];

        return $resourceMap[$resource] ?? $resource;
    }

    /**
     * Determine log category
     */
    private function getLogCategory(Request $request): string
    {
        $uri = $request->path();

        if (str_contains($uri, 'users')) return 'user_management';
        if (str_contains($uri, 'items')) return 'item_management';
        if (str_contains($uri, 'categories')) return 'category_management';
        if (str_contains($uri, 'requests')) return 'request_workflow';
        if (str_contains($uri, 'reports')) return 'report_access';
        if (str_contains($uri, 'login') || str_contains($uri, 'logout')) return 'authentication';
        if (str_contains($uri, 'qr')) return 'qr_operations';

        return 'general_activity';
    }

    /**
     * Sanitize input data for logging
     */
    private function sanitizeInput(array $input): array
    {
        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            '_token',
            '_method',
            'signature_data' // Don't log full signature data
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '[REDACTED]';
            }
        }

        // Limit input size to prevent large data from being logged
        $maxSize = 1000; // characters
        $serialized = json_encode($input);
        
        if (strlen($serialized) > $maxSize) {
            $input = ['message' => 'Input data too large to log completely', 'size' => strlen($serialized)];
        }

        return $input;
    }
}
