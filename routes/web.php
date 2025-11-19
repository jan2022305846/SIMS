<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\RequestController;
use App\Http\Controllers\Web\ReportsController;
use App\Http\Controllers\Web\HelpController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Auth\PasswordController;

// Custom Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
// Handle GET requests to logout (for bookmarks/direct access) - use same controller method
Route::get('logout', [LoginController::class, 'logout'])->name('logout.get');

// Password Reset Routes
Route::get('password/forgot', [PasswordController::class, 'showForgotForm'])->name('password.forgot');
Route::post('password/forgot', [PasswordController::class, 'sendResetLink'])->name('password.forgot.send');
Route::get('password/set/{token}', [PasswordController::class, 'showSetForm'])->name('password.set.form');
Route::post('password/set', [PasswordController::class, 'set'])->name('password.set');

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'showIndex'])->name('notifications.index');
    Route::get('/notifications/data', [NotificationController::class, 'index'])->name('notifications.data');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
    
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
    
    // Admin routes
    Route::middleware(['admin'])->group(function () {
        // Users
        Route::resource('users', UserController::class);
        Route::get('users/{user}/export/fulfilled', [UserController::class, 'exportFulfilledRequests'])->name('admin.users.export.fulfilled');
        
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Items
        Route::get('items/summary', [ItemController::class, 'summary'])->name('items.summary');
        Route::get('items/low-stock', [ItemController::class, 'lowStock'])->name('items.low-stock');
        Route::get('items/expiring-soon', [ItemController::class, 'expiringSoon'])->name('items.expiring-soon');
        Route::get('items/trashed', [ItemController::class, 'trashed'])->name('items.trashed');
        Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
        Route::post('items/bulk-restore', [ItemController::class, 'bulkRestore'])->name('items.bulk-restore');
        Route::delete('items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.force-delete');
        Route::post('items/bulk-force-delete', [ItemController::class, 'bulkForceDelete'])->name('items.bulk-force-delete');
        Route::post('items/import',[ItemController::class,'import'])->name('items.import');
        Route::get('items/download-template', [ItemController::class, 'downloadTemplate'])->name('items.download-template');

        // Item Assignment Management
        Route::get('items/{id}/assign', [ItemController::class, 'showAssignForm'])->name('items.assign');
        Route::post('items/{id}/assign', [ItemController::class, 'assign'])->name('items.assign.store');
        Route::delete('items/{id}/unassign', [ItemController::class, 'unassign'])->name('items.unassign');
        Route::patch('items/{id}/location', [ItemController::class, 'updateLocation'])->name('items.update-location');
        
        Route::resource('items', ItemController::class);
        
        // Requests Management
        Route::get('requests/manage', [RequestController::class, 'manage'])->name('requests.manage');
        Route::put('requests/{request}/status', [RequestController::class, 'updateStatus'])->name('requests.update-status');
        
        // New workflow actions
        Route::post('requests/{request}/approve-admin', [RequestController::class, 'approveByAdmin'])->name('requests.approve-admin');
        Route::post('requests/{request}/fulfill', [RequestController::class, 'fulfill'])->name('requests.fulfill');
        Route::post('requests/{request}/complete', [RequestController::class, 'completeAndClaim'])->name('requests.complete');
        Route::post('requests/{request}/claim', [RequestController::class, 'markAsClaimed'])->name('requests.claim');
        Route::post('requests/{request}/decline', [RequestController::class, 'decline'])->name('requests.decline');
        Route::delete('requests/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');
        
        // Add explicit show route
        Route::get('requests/{request}/details', [RequestController::class, 'show'])->name('requests.show');
        
        // Add claim slip download route for admins
        Route::get('requests/{request}/download-claim-slip', [RequestController::class, 'downloadClaimSlip'])->name('admin.requests.download-claim-slip');
        
        // Admin Reports - Main Reports Dashboard and Analytics
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/inventory-summary', [ReportsController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('reports/low-stock-alert', [ReportsController::class, 'lowStockAlert'])->name('reports.low-stock-alert');
        Route::get('reports/request-analytics', [ReportsController::class, 'requestAnalytics'])->name('reports.request-analytics');
        Route::get('reports/user-activity', [ReportsController::class, 'userActivityReport'])->name('reports.user-activity');
        Route::get('reports/dashboard-data', [ReportsController::class, 'dashboardData'])->name('reports.dashboard-data');
        
        // Admin Reports - PDF Downloads
        Route::get('reports/download', [ReportsController::class, 'downloadReport'])->name('reports.download');
        Route::get('reports/export', [ReportsController::class, 'exportReport'])->name('reports.export');
        
        // Admin Reports - QR Scan Reports Only
        Route::get('reports/qr-scan-analytics', [ReportsController::class, 'qrScanAnalytics'])->name('reports.qr-scan-analytics');
        Route::get('reports/item-scan-history/{itemId}', [ReportsController::class, 'itemScanHistory'])->name('reports.item-scan-history');
        Route::get('reports/scan-alerts', [ReportsController::class, 'scanAlerts'])->name('reports.scan-alerts');
        Route::get('reports/monthly-summary', [ReportsController::class, 'monthlySummary'])->name('reports.monthly-summary');
        Route::get('reports/quarterly-summary', [ReportsController::class, 'quarterlySummary'])->name('reports.quarterly-summary');
        Route::get('reports/annual-summary', [ReportsController::class, 'annualSummary'])->name('reports.annual-summary');
        
        // QR Scan Logs Downloads
        Route::get('reports/qr-scan-logs/download', [ReportsController::class, 'downloadQrScanLogs'])->name('reports.qr-scan-logs.download');
        Route::get('reports/qr-scan-logs/export', [ReportsController::class, 'exportQrScanLogs'])->name('reports.qr-scan-logs.export');
    });
    
    // Item verification (accessible to authenticated users for barcode scanning)
    Route::middleware(['auth'])->group(function () {
        Route::get('items/verify-barcode/{barcode}', [ItemController::class, 'verifyBarcode'])->name('items.verify-barcode');
    });
    
    // Faculty routes (includes admin access)
    Route::middleware(['faculty'])->group(function () {
        // Items browsing for faculty
        Route::get('browse-items', [ItemController::class, 'browse'])->name('faculty.items.index');
        Route::get('faculty/items', [ItemController::class, 'browse'])->name('faculty.items.browse');
        Route::get('faculty/items/{id}', [ItemController::class, 'show'])->name('faculty.items.show');
        
        // Requests
        Route::get('my-requests', [RequestController::class, 'myRequests'])->name('faculty.requests.index');
        Route::get('faculty/requests', [RequestController::class, 'myRequests'])->name('faculty.requests.my');
        Route::get('faculty/requests/create', [RequestController::class, 'create'])->name('faculty.requests.create');
        Route::post('faculty/requests', [RequestController::class, 'store'])->name('faculty.requests.store');
        Route::get('faculty/requests/{request}', [RequestController::class, 'show'])->name('faculty.requests.show');
        Route::get('faculty/requests/{request}/edit', [RequestController::class, 'editFaculty'])->name('faculty.requests.edit');
        Route::put('faculty/requests/{request}', [RequestController::class, 'updateFaculty'])->name('faculty.requests.update');
        Route::post('faculty/requests/{request}/cancel', [RequestController::class, 'cancelFaculty'])->name('faculty.requests.cancel');
        Route::post('faculty/requests/{request}/generate-claim-slip', [RequestController::class, 'generateClaimSlip'])->name('faculty.requests.generate-claim-slip');
        Route::get('faculty/requests/{request}/download-claim-slip', [RequestController::class, 'downloadClaimSlip'])->name('faculty.requests.download-claim-slip');
        Route::delete('faculty/requests/{request}', [RequestController::class, 'destroy'])->name('faculty.requests.destroy');
        
        // Claim slip printing (accessible by faculty and admin)
        Route::get('requests/{request}/claim-slip', [RequestController::class, 'printClaimSlip'])->name('requests.claim-slip');
        
        // QR Code scanner interface
        Route::get('qr/scanner', [QRCodeController::class, 'scanner'])->name('qr.scanner');
        Route::get('qr/test', [QRCodeController::class, 'test'])->name('qr.test');
        Route::get('qr/simple-test', [QRCodeController::class, 'simpleTest'])->name('qr.simple-test');
        Route::post('qr/scan', [QRCodeController::class, 'scan'])->name('qr.scan');
    });
    
    // Admin QR code routes
    Route::middleware(['admin'])->group(function () {
        Route::post('qr/generate/{id}', [QRCodeController::class, 'generate'])->name('qr.generate');
        Route::get('qr/download/{id}', [QRCodeController::class, 'download'])->name('qr.download');
    });
});

// Include debug routes only in non-production environments
if (config('app.debug') || config('app.env') !== 'production') {
    require __DIR__ . '/debug.php';
}