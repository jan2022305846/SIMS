<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// Debug/Health Check Route (only in non-production or when APP_DEBUG is true)
Route::get('/debug/health', function (Request $request) {
    $checks = [];
    
    // Database connection check
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'Connected';
    } catch (\Exception $e) {
        $checks['database'] = 'Failed: ' . $e->getMessage();
    }
    
    // Storage permissions check
    $checks['storage_writable'] = is_writable(storage_path()) ? 'Yes' : 'No';
    $checks['cache_writable'] = is_writable(storage_path('framework/cache')) ? 'Yes' : 'No';
    $checks['logs_writable'] = is_writable(storage_path('logs')) ? 'Yes' : 'No';
    
    // Environment checks
    $checks['app_key'] = config('app.key') ? 'Set' : 'Missing';
    $checks['app_debug'] = config('app.debug') ? 'Enabled' : 'Disabled';
    $checks['app_env'] = config('app.env');
    $checks['php_version'] = PHP_VERSION;
    
    // Extension checks
    $checks['extensions'] = [
        'pdo_mysql' => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing',
        'mbstring' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
        'openssl' => extension_loaded('openssl') ? 'Loaded' : 'Missing',
        'gd' => extension_loaded('gd') ? 'Loaded' : 'Missing',
    ];
    
    return response()->json([
        'status' => 'Laravel Application Health Check',
        'timestamp' => now()->toISOString(),
        'checks' => $checks
    ]);
})->middleware('web');

// Simple info endpoint
Route::get('/debug/info', function () {
    return response()->json([
        'app_name' => config('app.name'),
        'app_env' => config('app.env'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'timestamp' => now()->toISOString(),
    ]);
})->middleware('web');
