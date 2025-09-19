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
use App\Http\Controllers\Web\AcknowledgmentController;
use App\Http\Controllers\Web\HelpController;
use App\Http\Controllers\Web\BackupController;
use App\Http\Controllers\Web\RestoreController;
use App\Http\Controllers\QRCodeController;

// Custom Authentication Routes
Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [CustomLoginController::class, 'login']);
Route::post('logout', [CustomLoginController::class, 'logout'])->name('logout');
// Handle GET requests to logout (for bookmarks/direct access) - use same controller method
Route::get('logout', [CustomLoginController::class, 'logout'])->name('logout.get');

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Enhanced Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/scan-qr', [DashboardController::class, 'scanQR'])->name('dashboard.scan-qr');
    
    // Dashboard AJAX endpoints
    Route::get('/dashboard/api/low-stock-alerts', [DashboardController::class, 'lowStockAlerts'])->name('dashboard.api.low-stock');
    Route::get('/dashboard/api/pending-requests', [DashboardController::class, 'pendingRequests'])->name('dashboard.api.pending-requests');
    Route::get('/dashboard/api/recent-activities', [DashboardController::class, 'recentActivities'])->name('dashboard.api.activities');
    Route::get('/dashboard/api/statistics', [DashboardController::class, 'statistics'])->name('dashboard.api.statistics');
    Route::get('/dashboard/api/quick-actions', [DashboardController::class, 'quickActions'])->name('dashboard.api.quick-actions');
    Route::get('/dashboard/api/stock-overview', [DashboardController::class, 'stockOverview'])->name('dashboard.api.stock-overview');
    Route::get('/dashboard/api/notifications', [DashboardController::class, 'notifications'])->name('dashboard.api.notifications');
    
    // Admin-only dashboard endpoints
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard/api/system-health', [DashboardController::class, 'systemHealth'])->name('dashboard.api.system-health');
    });
    
    // Help System Routes
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/{topic}', [HelpController::class, 'show'])->name('help.show');
    Route::get('/help/api/search', [HelpController::class, 'search'])->name('help.search');
    
    // Backup & Restore Routes (Admin only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/backup', [BackupController::class, 'index'])->name('admin.backup.index');
        Route::post('/backup/create', [BackupController::class, 'create'])->name('admin.backup.create');
        Route::post('/backup/create-full', [BackupController::class, 'createFullBackup'])->name('admin.backup.create-full');
        Route::post('/backup/create-selective', [BackupController::class, 'createSelectiveBackup'])->name('admin.backup.create-selective');
        Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('admin.backup.download');
        Route::delete('/backup/delete/{filename}', [BackupController::class, 'delete'])->name('admin.backup.delete');
        
        Route::get('/restore', [RestoreController::class, 'index'])->name('admin.restore.index');
        Route::post('/restore/upload', [RestoreController::class, 'upload'])->name('admin.restore.upload');
        Route::post('/restore/analyze', [RestoreController::class, 'analyze'])->name('admin.restore.analyze');
        Route::post('/restore/safety-backup', [RestoreController::class, 'createSafetyBackup'])->name('admin.restore.safety-backup');
        Route::post('/restore/execute', [RestoreController::class, 'restore'])->name('admin.restore.execute');
    });
    
    // Admin routes
    Route::middleware(['admin'])->group(function () {
        // Users
        Route::resource('users', UserController::class);
        
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Items
        Route::get('items/summary', [ItemController::class, 'summary'])->name('items.summary');
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
        
        // Request Acknowledgments (Digital Signatures)
        Route::get('requests/{request}/acknowledgment', [AcknowledgmentController::class, 'show'])->name('requests.acknowledgment.show');
        Route::post('requests/{request}/acknowledgment', [AcknowledgmentController::class, 'store'])->name('requests.acknowledgment.store');
        Route::get('requests/{request}/receipt', [AcknowledgmentController::class, 'receipt'])->name('requests.acknowledgment.receipt');
        Route::get('requests/{request}/receipt/download', [AcknowledgmentController::class, 'downloadReceipt'])->name('requests.acknowledgment.download');
        Route::get('requests/{request}/verify', [AcknowledgmentController::class, 'verify'])->name('requests.acknowledgment.verify');
        
        // Admin Reports
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/dashboard-data', [ReportsController::class, 'dashboardData'])->name('reports.dashboard-data');
        Route::get('reports/daily-csv', [ReportsController::class, 'dailyCsv'])->name('reports.daily-csv');
        Route::get('reports/weekly-csv', [ReportsController::class, 'weeklyCsv'])->name('reports.weekly-csv');
        Route::get('reports/annual-csv', [ReportsController::class, 'annualCsv'])->name('reports.annual-csv');
        Route::get('reports/inventory-summary', [ReportsController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('reports/low-stock-alert', [ReportsController::class, 'lowStockAlert'])->name('reports.low-stock-alert');
        Route::get('reports/daily-transactions', [ReportsController::class, 'dailyTransactions'])->name('reports.daily-transactions');
        Route::get('reports/daily-disbursement', [ReportsController::class, 'dailyDisbursement'])->name('reports.daily-disbursement');
        Route::get('reports/weekly-summary', [ReportsController::class, 'weeklySummary'])->name('reports.weekly-summary');
        Route::get('reports/weekly-requests', [ReportsController::class, 'weeklyRequests'])->name('reports.weekly-requests');
        Route::get('reports/monthly-summary', [ReportsController::class, 'monthlySummary'])->name('reports.monthly-summary');
        Route::get('reports/annual-summary', [ReportsController::class, 'annualSummary'])->name('reports.annual-summary');
        
        Route::resource('requests', RequestController::class)->except(['create', 'store', 'show']);
    });
    
    // Office Head routes
    Route::middleware(['office_head'])->group(function () {
        Route::get('office-head/requests', [RequestController::class, 'index'])->name('office-head.requests');
        Route::post('requests/{request}/approve-office-head', [RequestController::class, 'approveByOfficeHead'])->name('requests.approve-office-head');
        Route::post('requests/{request}/decline-office-head', [RequestController::class, 'decline'])->name('requests.decline-office-head');
        Route::get('requests/{request}', [RequestController::class, 'show'])->name('office-head.requests.show');
        
        // Reports
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/dashboard-data', [ReportsController::class, 'dashboardData'])->name('reports.dashboard-data');
        Route::get('reports/daily-csv', [ReportsController::class, 'dailyCsv'])->name('reports.daily-csv');
        Route::get('reports/weekly-csv', [ReportsController::class, 'weeklyCsv'])->name('reports.weekly-csv');
        Route::get('reports/annual-csv', [ReportsController::class, 'annualCsv'])->name('reports.annual-csv');
        Route::get('reports/inventory-summary', [ReportsController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('reports/low-stock-alert', [ReportsController::class, 'lowStockAlert'])->name('reports.low-stock-alert');
        
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
        // Items browsing for faculty
        Route::get('browse-items', [ItemController::class, 'browse'])->name('faculty.items.index');
        Route::get('faculty/items', [ItemController::class, 'browse'])->name('faculty.items.browse');
        Route::get('faculty/items/{item}', [ItemController::class, 'show'])->name('faculty.items.show');
        
        // Requests
        Route::get('my-requests', [RequestController::class, 'myRequests'])->name('faculty.requests.index');
        Route::get('faculty/requests', [RequestController::class, 'myRequests'])->name('faculty.requests.my');
        Route::get('faculty/requests/create', [RequestController::class, 'create'])->name('faculty.requests.create');
        Route::post('faculty/requests', [RequestController::class, 'store'])->name('faculty.requests.store');
        Route::get('faculty/requests/{request}', [RequestController::class, 'show'])->name('faculty.requests.show');
        
        // Faculty acknowledgment access (for their own requests)
        Route::get('requests/{request}/acknowledgment', [AcknowledgmentController::class, 'show'])->name('faculty.requests.acknowledgment.show');
        Route::post('requests/{request}/acknowledgment', [AcknowledgmentController::class, 'store'])->name('faculty.requests.acknowledgment.store');
        Route::get('requests/{request}/receipt', [AcknowledgmentController::class, 'receipt'])->name('faculty.requests.acknowledgment.receipt');
        Route::get('requests/{request}/receipt/download', [AcknowledgmentController::class, 'downloadReceipt'])->name('faculty.requests.acknowledgment.download');
        
        // QR Code scanner interface
        Route::get('qr/scanner', [QRCodeController::class, 'scanner'])->name('qr.scanner');
        Route::get('qr/test', [QRCodeController::class, 'test'])->name('qr.test');
        Route::get('qr/simple-test', [QRCodeController::class, 'simpleTest'])->name('qr.simple-test');
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
        Route::get('activity-logs/security/report', [App\Http\Controllers\ActivityLogController::class, 'securityReport'])->name('activity-logs.security');
        Route::get('activity-logs/export/csv', [App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('activity-logs/live/feed', [App\Http\Controllers\ActivityLogController::class, 'liveFeed'])->name('activity-logs.live-feed');
        Route::post('activity-logs/cleanup', [App\Http\Controllers\ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');
        Route::get('users/{user}/activity', [App\Http\Controllers\ActivityLogController::class, 'userActivity'])->name('activity-logs.user-activity');
    });
});

// Include debug routes only in non-production environments
if (config('app.debug') || config('app.env') !== 'production') {
    require __DIR__ . '/debug.php';
}