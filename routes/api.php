<?php

declare(strict_types=1);

use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\MarketplaceConnectionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::middleware(['has_org'])->group(function (): void {
        Route::apiResource('marketplace-connections', MarketplaceConnectionController::class)->names('api.marketplace-connections');
    });
});

require __DIR__.'/webhooks.php';
require __DIR__.'/mock.php';
