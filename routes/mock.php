<?php

declare(strict_types=1);

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
    });
