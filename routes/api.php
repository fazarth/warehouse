<?php

use App\Http\Controllers\Api\ErpApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — W6 ERP integration (Sanctum)
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [ErpApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ErpApiController::class, 'me']);
    Route::get('/products', [ErpApiController::class, 'products']);
    Route::get('/stock', [ErpApiController::class, 'stock']);
    Route::get('/purchase-orders', [ErpApiController::class, 'purchaseOrders']);
    Route::get('/goods-receipts', [ErpApiController::class, 'goodsReceipts']);
    Route::get('/goods-issues', [ErpApiController::class, 'goodsIssues']);
    Route::get('/mutations', [ErpApiController::class, 'mutations']);
    Route::get('/aging', [ErpApiController::class, 'aging']);
    Route::post('/scan', [ErpApiController::class, 'scan']);
});
