<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users and successful requests
        if (Auth::check() && $response->getStatusCode() < 400) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    protected function logActivity(Request $request, Response $response): void
    {
        $method = $request->method();
        $path = $request->path();
        $user = Auth::user();
        
        // Skip logging for certain routes
        $skipRoutes = [
            'build/assets',
            'favicon.ico',
        ];

        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($path, $skipRoute)) {
                return;
            }
        }

        // Generate description based on the request
        $description = $this->generateDescription($method, $path, $request);

        if ($description) {
            $log = ActivityLog::log($description)
                ->inLog('user_activity')
                ->withProperties([
                    'method' => $method,
                    'path' => $path,
                    'url' => $request->fullUrl(),
                    'status_code' => $response->getStatusCode(),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ]);
            
            if ($user instanceof User) {
                $log->causedBy($user);
            }
            
            $log->save();
        }
    }

    protected function generateDescription(string $method, string $path, Request $request): ?string
    {
        // Login/Logout activities
        if ($path === 'login' && $method === 'POST') {
            return '{causer} logged into the system';
        }

        if ($path === 'logout' && $method === 'POST') {
            return '{causer} logged out of the system';
        }

        // Dashboard access
        if ($path === 'dashboard') {
            return '{causer} accessed the dashboard';
        }

        // User management
        if (str_starts_with($path, 'users')) {
            if ($method === 'GET' && $path === 'users') {
                return '{causer} viewed user list';
            }
            if ($method === 'GET' && preg_match('/users\/\d+/', $path)) {
                return '{causer} viewed user profile';
            }
            if ($method === 'POST' && $path === 'users') {
                return '{causer} created a new user';
            }
            if ($method === 'PUT' && preg_match('/users\/\d+/', $path)) {
                return '{causer} updated user information';
            }
            if ($method === 'DELETE' && preg_match('/users\/\d+/', $path)) {
                return '{causer} deleted a user';
            }
        }

        // Item management
        if (str_starts_with($path, 'items')) {
            if ($method === 'GET' && $path === 'items') {
                return '{causer} viewed items list';
            }
            if ($method === 'GET' && preg_match('/items\/\d+/', $path)) {
                return '{causer} viewed item details';
            }
            if ($method === 'POST' && $path === 'items') {
                return '{causer} created a new item';
            }
            if ($method === 'PUT' && preg_match('/items\/\d+/', $path)) {
                return '{causer} updated item information';
            }
            if ($method === 'DELETE' && preg_match('/items\/\d+/', $path)) {
                return '{causer} deleted an item';
            }
            if ($path === 'items/browse') {
                return '{causer} browsed available items';
            }
        }

        // Category management
        if (str_starts_with($path, 'categories')) {
            if ($method === 'GET' && $path === 'categories') {
                return '{causer} viewed categories list';
            }
            if ($method === 'POST' && $path === 'categories') {
                return '{causer} created a new category';
            }
            if ($method === 'PUT' && preg_match('/categories\/\d+/', $path)) {
                return '{causer} updated category information';
            }
            if ($method === 'DELETE' && preg_match('/categories\/\d+/', $path)) {
                return '{causer} deleted a category';
            }
        }

        // Request management
        if (str_starts_with($path, 'requests')) {
            if ($method === 'GET' && $path === 'requests/manage') {
                return '{causer} accessed request management';
            }
            if ($method === 'GET' && $path === 'requests/my') {
                return '{causer} viewed their requests';
            }
            if ($method === 'POST' && $path === 'requests') {
                return '{causer} submitted a new request';
            }
            if ($method === 'PUT' && preg_match('/requests\/\d+\/approve-office-head/', $path)) {
                return '{causer} approved request as Office Head';
            }
            if ($method === 'PUT' && preg_match('/requests\/\d+\/approve-admin/', $path)) {
                return '{causer} approved request as Admin';
            }
            if ($method === 'PUT' && preg_match('/requests\/\d+\/fulfill/', $path)) {
                return '{causer} fulfilled a request';
            }
            if ($method === 'PUT' && preg_match('/requests\/\d+\/claim/', $path)) {
                return '{causer} marked request as claimed';
            }
            if ($method === 'PUT' && preg_match('/requests\/\d+\/decline/', $path)) {
                return '{causer} declined a request';
            }
        }

        // QR Scanner
        if (str_starts_with($path, 'qr')) {
            if ($path === 'qr/scanner') {
                return '{causer} accessed QR code scanner';
            }
            if ($method === 'POST' && $path === 'qr/scan') {
                return '{causer} scanned a QR code';
            }
        }

        // Reports
        if (str_starts_with($path, 'reports')) {
            if ($path === 'reports') {
                return '{causer} accessed reports dashboard';
            }
            if (str_contains($path, 'inventory-summary')) {
                return '{causer} generated inventory summary report';
            }
            if (str_contains($path, 'low-stock-alert')) {
                return '{causer} generated low stock alert report';
            }
            if (str_contains($path, 'financial-summary')) {
                return '{causer} generated financial summary report';
            }
            if (str_contains($path, 'request-analytics')) {
                return '{causer} generated request analytics report';
            }
        }

        return null;
    }
}
