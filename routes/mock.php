<?php

declare(strict_types=1);

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
        //
    });
