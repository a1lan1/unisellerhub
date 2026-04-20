<?php

declare(strict_types=1);

use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\MarketplaceConnectionController;
use App\Modules\User\Interfaces\Http\Controllers\Api\NotificationController;
use App\Modules\User\Interfaces\Http\Controllers\Api\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('organizations', [OrganizationController::class, 'store'])->name('api.organizations.store');

    // Notifications
    Route::prefix('notifications')->name('api.notifications.')->group(function (): void {
        Route::get('', [NotificationController::class, 'index'])->name('index');
        Route::post('read', [NotificationController::class, 'markAllAsRead'])->name('read_all');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['has_org'])->group(function (): void {
        Route::apiResource('marketplace-connections', MarketplaceConnectionController::class)->names('api.marketplace-connections');
    });
});

require __DIR__.'/webhooks.php';
require __DIR__.'/mock.php';
