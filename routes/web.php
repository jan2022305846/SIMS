<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\RequestController;
use App\Http\Controllers\Web\ReportsController;
use App\Http\Controllers\QRCodeController;

// Custom Authentication Routes
Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [CustomLoginController::class, 'login']);
Route::post('logout', [CustomLoginController::class, 'logout'])->name('logout');

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/scan-qr', [DashboardController::class, 'scanQR'])->name('dashboard.scan-qr');
    
    // Admin routes
    Route::middleware(['admin'])->group(function () {
        // Users
        Route::resource('users', UserController::class);
        
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Items
        Route::get('items/low-stock', [ItemController::class, 'lowStock'])->name('items.low-stock');
        Route::get('items/expiring-soon', [ItemController::class, 'expiringSoon'])->name('items.expiring-soon');
        Route::get('items/trashed', [ItemController::class, 'trashed'])->name('items.trashed');
        Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
        Route::resource('items', ItemController::class);
        
        // Requests Management
        Route::get('requests/manage', [RequestController::class, 'manage'])->name('requests.manage');
        Route::get('requests/{request}/claim-slip', [RequestController::class, 'printClaimSlip'])->name('requests.claim-slip');
        Route::put('requests/{request}/status', [RequestController::class, 'updateStatus'])->name('requests.update-status');
        
        // New workflow actions
        Route::post('requests/{request}/approve-admin', [RequestController::class, 'approveByAdmin'])->name('requests.approve-admin');
        Route::post('requests/{request}/fulfill', [RequestController::class, 'fulfill'])->name('requests.fulfill');
        Route::post('requests/{request}/claim', [RequestController::class, 'markAsClaimed'])->name('requests.claim');
        Route::post('requests/{request}/decline', [RequestController::class, 'decline'])->name('requests.decline');
        
        // Add explicit show route
        Route::get('requests/{request}/details', [RequestController::class, 'show'])->name('requests.show');
        
        Route::resource('requests', RequestController::class)->except(['create', 'store', 'show']);
    });
    
    // Office Head routes
    Route::middleware(['office_head'])->group(function () {
        Route::get('office-head/requests', [RequestController::class, 'index'])->name('office-head.requests');
        Route::post('requests/{request}/approve-office-head', [RequestController::class, 'approveByOfficeHead'])->name('requests.approve-office-head');
        Route::post('requests/{request}/decline-office-head', [RequestController::class, 'decline'])->name('requests.decline-office-head');
        Route::get('requests/{request}', [RequestController::class, 'show'])->name('office-head.requests.show');
        
        // Reports
        Route::get('reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('reports/inventory-summary', [ReportsController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('reports/low-stock-alert', [ReportsController::class, 'lowStockAlert'])->name('reports.low-stock-alert');
        Route::get('reports/request-analytics', [ReportsController::class, 'requestAnalytics'])->name('reports.request-analytics');
        Route::get('reports/department-report', [ReportsController::class, 'departmentReport'])->name('reports.department-report');
        Route::get('reports/financial-summary', [ReportsController::class, 'financialSummary'])->name('reports.financial-summary');
        Route::get('reports/user-activity', [ReportsController::class, 'userActivityReport'])->name('reports.user-activity');
        Route::get('reports/custom-report', [ReportsController::class, 'customReport'])->name('reports.custom-report');
        
        // New Daily, Weekly, Monthly, Annual Reports
        Route::get('reports/daily-transactions', [ReportsController::class, 'dailyTransactions'])->name('reports.daily-transactions');
        Route::get('reports/daily-disbursement', [ReportsController::class, 'dailyDisbursement'])->name('reports.daily-disbursement');
        Route::get('reports/weekly-summary', [ReportsController::class, 'weeklySummary'])->name('reports.weekly-summary');
        Route::get('reports/weekly-requests', [ReportsController::class, 'weeklyRequests'])->name('reports.weekly-requests');
        Route::get('reports/monthly-summary', [ReportsController::class, 'monthlySummary'])->name('reports.monthly-summary');
        Route::get('reports/annual-summary', [ReportsController::class, 'annualSummary'])->name('reports.annual-summary');
    });
    
    // Faculty routes (includes admin access)
    Route::middleware(['faculty'])->group(function () {
        Route::get('browse-items', [ItemController::class, 'browse'])->name('items.browse');
        Route::get('my-requests', [RequestController::class, 'myRequests'])->name('requests.my');
        Route::get('requests/create', [RequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [RequestController::class, 'store'])->name('requests.store');
        
        // QR Code scanner interface
        Route::get('qr/scanner', [QRCodeController::class, 'scanner'])->name('qr.scanner');
        Route::post('qr/scan', [QRCodeController::class, 'scan'])->name('qr.scan');
    });
    
    // Admin QR code routes
    Route::middleware(['admin'])->group(function () {
        Route::post('qr/generate/{item}', [QRCodeController::class, 'generate'])->name('qr.generate');
        Route::get('qr/download/{item}', [QRCodeController::class, 'download'])->name('qr.download');
        
        // Activity Logs (Admin only)
        Route::get('activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/{activityLog}', [App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::get('activity-logs/analytics/dashboard', [App\Http\Controllers\ActivityLogController::class, 'analytics'])->name('activity-logs.analytics');
        Route::post('activity-logs/cleanup', [App\Http\Controllers\ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');
        Route::get('users/{user}/activity', [App\Http\Controllers\ActivityLogController::class, 'userActivity'])->name('activity-logs.user-activity');
    });
});

// Include debug routes only in non-production environments
if (config('app.debug') || config('app.env') !== 'production') {
    require __DIR__ . '/debug.php';
}
