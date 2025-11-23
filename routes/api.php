<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Web\ReportsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Item lookup for barcode/QR scanning
Route::get('/items/lookup/{code}', [ItemController::class, 'lookup'])
    ->name('api.items.lookup');

// Item verification for barcode scanning
Route::get('/items/verify-barcode/{barcode}', [ItemController::class, 'verifyBarcode'])
    ->name('api.items.verify-barcode');

// Reports API endpoints
Route::middleware(['auth:web'])->group(function () {
    Route::get('/reports/inventory-data', [ReportsController::class, 'getInventoryData'])
        ->name('api.reports.inventory-data');
});
// QR scan data moved to web routes for proper session auth