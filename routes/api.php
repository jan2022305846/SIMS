<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Web\OfficeController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\InventoryManagementController;
use App\Http\Controllers\Admin\RequestManagementController;
use App\Http\Controllers\Admin\ReportController;

//AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgot']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // User Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Users API
    Route::apiResource('api-users', UserController::class)->names([
        'index' => 'api.users.index',
        'store' => 'api.users.store',
        'show' => 'api.users.show',
        'update' => 'api.users.update',
        'destroy' => 'api.users.destroy'
    ]);
    
    // Categories API
    Route::apiResource('api-categories', CategoryController::class)->names([
        'index' => 'api.categories.index',
        'store' => 'api.categories.store',
        'show' => 'api.categories.show',
        'update' => 'api.categories.update',
        'destroy' => 'api.categories.destroy'
    ]);
    
    // Items
    Route::get('/items/search', [ItemController::class, 'search']);
    Route::get('/items/low-stock', [ItemController::class, 'lowStock']); 
    Route::get('/items/category/{categoryId}', [ItemController::class, 'byCategory']);
    Route::get('/items/expiring-soon', [ItemController::class, 'expiringSoon']);
    Route::get('/items/trashed', [ItemController::class, 'trashed']);
    Route::post('/items/{id}/restore', [ItemController::class, 'restore']);
    // Items API
    Route::apiResource('api-items', ItemController::class)->names([
        'index' => 'api.items.index',
        'store' => 'api.items.store',
        'show' => 'api.items.show',
        'update' => 'api.items.update',
        'destroy' => 'api.items.destroy'
    ]);
    
    // Requests
    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/my-requests', [RequestController::class, 'myRequests']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::put('/requests/{id}/status', [RequestController::class, 'updateStatus']);
    
    // Logs
    Route::get('/logs', [LogController::class, 'index']);
    Route::post('/logs', [LogController::class, 'store']);

    // QR Code
    Route::post('/qr-code/scan', [QRCodeController::class, 'scan']);
    Route::get('/qr-code/generate/{itemId}', [QRCodeController::class, 'generate']);

    // Offices
    Route::apiResource('offices', OfficeController::class);
    
    // Admin Routes - Protected by admin middleware
    Route::prefix('admin')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/statistics', [AdminController::class, 'getStatistics']);
        Route::get('/low-stock-items', [AdminController::class, 'getLowStockItems']);
        Route::get('/expiring-items', [AdminController::class, 'getExpiringItems']);
        Route::post('/scan-qr', [AdminController::class, 'scanQRCode']);
        Route::post('/quick-scan', [AdminController::class, 'quickScan']);
        Route::get('/qr-scanner-data', [AdminController::class, 'getQRScannerData']);
        Route::post('/items/{item}/generate-qr', [AdminController::class, 'generateQRCode']);
        Route::post('/bulk-generate-qr', [AdminController::class, 'bulkGenerateQRCodes']);
        Route::post('/change-password', [AdminController::class, 'changePassword']);
        Route::post('/logout', [AdminController::class, 'logout']);

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']);
            Route::post('/', [UserManagementController::class, 'store']);
            Route::get('/statistics', [UserManagementController::class, 'statistics']);
            Route::get('/offices', [UserManagementController::class, 'getOffices']);
            Route::get('/{user}', [UserManagementController::class, 'show']);
            Route::put('/{user}', [UserManagementController::class, 'update']);
            Route::delete('/{user}', [UserManagementController::class, 'destroy']);
            Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword']);
        });

        // Inventory Management
        Route::prefix('inventory')->group(function () {
            Route::get('/', [InventoryManagementController::class, 'index']);
            Route::get('/statistics', [InventoryManagementController::class, 'statistics']);
            Route::get('/low-stock', [InventoryManagementController::class, 'getLowStockItems']);
            Route::get('/expiring', [InventoryManagementController::class, 'getExpiringItems']);
            Route::get('/deleted', [InventoryManagementController::class, 'getDeletedItems']);
            Route::get('/holders', [InventoryManagementController::class, 'getAvailableHolders']);
            Route::put('/{item}/stock', [InventoryManagementController::class, 'updateStock']);
            Route::put('/{item}/transfer', [InventoryManagementController::class, 'transferItem']);
            Route::post('/{itemId}/restore', [InventoryManagementController::class, 'restoreItem']);
            Route::post('/bulk-update', [InventoryManagementController::class, 'bulkUpdate']);
        });

        // Request Management
        Route::prefix('requests')->group(function () {
            Route::get('/', [RequestManagementController::class, 'index']);
            Route::get('/statistics', [RequestManagementController::class, 'statistics']);
            Route::get('/pending', [RequestManagementController::class, 'getPendingRequests']);
            Route::get('/{request}', [RequestManagementController::class, 'show']);
            Route::post('/{request}/approve', [RequestManagementController::class, 'approve']);
            Route::post('/{request}/decline', [RequestManagementController::class, 'decline']);
            Route::post('/bulk-approve', [RequestManagementController::class, 'bulkApprove']);
            Route::get('/user/{user}/history', [RequestManagementController::class, 'getUserRequestHistory']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/inventory', [ReportController::class, 'inventoryReport']);
            Route::get('/user-activity', [ReportController::class, 'userActivityReport']);
            Route::get('/request-history', [ReportController::class, 'requestHistoryReport']);
            Route::get('/scan-logs', [ReportController::class, 'scanLogsReport']);
            Route::get('/system', [ReportController::class, 'systemReport']);
        });
    });
});