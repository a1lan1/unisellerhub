<?php

declare(strict_types=1);

use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\MarketplaceConnectionController;
use App\Modules\User\Interfaces\Http\Controllers\Api\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('organizations', [OrganizationController::class, 'store'])->name('api.organizations.store');

    Route::middleware(['has_org'])->group(function (): void {
        Route::apiResource('marketplace-connections', MarketplaceConnectionController::class)->names('api.marketplace-connections');
    });
});

require __DIR__.'/webhooks.php';
require __DIR__.'/mock.php';
