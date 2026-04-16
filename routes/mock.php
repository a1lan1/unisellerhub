<?php

declare(strict_types=1);

use App\Modules\MockMarketplace\Interfaces\Http\Controllers\OzonMockController;
use App\Modules\MockMarketplace\Interfaces\Http\Controllers\WbMockController;
use App\Modules\MockMarketplace\Interfaces\Http\Middleware\IdentifyMockMarketplaceAccount;
use App\Modules\MockMarketplace\Interfaces\Http\Middleware\MockIdempotencyMiddleware;
use App\Modules\MockMarketplace\Interfaces\Http\Middleware\MockPerformanceMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('mock')
    ->middleware([
        IdentifyMockMarketplaceAccount::class,
        MockPerformanceMiddleware::class,
        MockIdempotencyMiddleware::class,
    ])
    ->group(function (): void {
        // Wildberries API
        Route::prefix('wb')->group(function (): void {
            Route::get('/api/v3/stocks', [WbMockController::class, 'getStocks']);
            Route::put('/api/v3/stocks/{warehouseId}', [WbMockController::class, 'updateStocks']);
            Route::get('/api/v3/orders', [WbMockController::class, 'getOrders']);
            Route::post('/content/v2/get/cards/list', [WbMockController::class, 'getProducts']);
            Route::post('/public/api/v1/prices', [WbMockController::class, 'updatePrices']);
        });

        // Ozon API
        Route::prefix('ozon/v1')->group(function (): void {
            Route::post('/product/list', [OzonMockController::class, 'getProducts']);
            Route::post('/product/info/list', [OzonMockController::class, 'getProductDetails']);
            Route::post('/product/info/stocks', [OzonMockController::class, 'getStocks']);
            Route::post('/product/import/stocks', [OzonMockController::class, 'updateStocks']);
            Route::post('/product/import/prices', [OzonMockController::class, 'updatePrices']);
            Route::post('/posting/fbs/list', [OzonMockController::class, 'getOrders']);
        });
    });
