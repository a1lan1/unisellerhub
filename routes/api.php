<?php

declare(strict_types=1);

use App\Modules\Inventory\Interfaces\Http\Controllers\Api\InventorySyncController;
use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\MarketplaceConnectionController;
use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\SearchController;
use App\Modules\Order\Interfaces\Http\Controllers\Api\OrderSyncController;
use App\Modules\PriceAnalysis\Interfaces\Http\Controllers\Api\PriceAnalysisController;
use App\Modules\Product\Interfaces\Http\Controllers\Api\ProductSyncController;
use App\Modules\Report\Interfaces\Http\Controllers\AnalyticsController;
use App\Modules\Report\Interfaces\Http\Controllers\Api\ExportController;
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
        Route::delete('', [NotificationController::class, 'clearAll'])->name('clear_all');
    });

    Route::middleware(['has_org'])->group(function (): void {
        Route::get('search', SearchController::class)->name('api.search');

        Route::apiResource('marketplace-connections', MarketplaceConnectionController::class)->names('api.marketplace-connections');

        // Products
        Route::prefix('products')->name('api.products.')->group(function (): void {
            Route::post('sync', [ProductSyncController::class, 'sync'])->name('sync');
            Route::post('sync-bulk', [ProductSyncController::class, 'syncBulk'])->name('sync_bulk');
        });

        // Orders
        Route::post('orders/sync', [OrderSyncController::class, 'sync'])->name('api.orders.sync');

        // Inventory
        Route::prefix('inventory')->name('api.inventory.')->group(function (): void {
            Route::post('pull', [InventorySyncController::class, 'pull'])->name('pull');
            Route::post('pull-bulk', [InventorySyncController::class, 'pullBulk'])->name('pull_bulk');
            Route::patch('update', [InventorySyncController::class, 'update'])->name('update');
            Route::post('sync-ms', [InventorySyncController::class, 'syncMoySkladStock'])->name('sync_ms');
        });

        // Exports
        Route::prefix('exports')->name('exports.')->group(function (): void {
            Route::post('orders', [ExportController::class, 'orders'])->name('orders');
            Route::post('inventory', [ExportController::class, 'inventory'])->name('inventory');
            Route::post('analytics', [ExportController::class, 'generateAnalyticsReport'])->name('analytics');
        });

        // Analytics
        Route::patch('analytics/update-finance', [AnalyticsController::class, 'updateFinance'])->name('api.analytics.update_finance');

        // Price Analysis
        Route::post('price-analysis/analyze', [PriceAnalysisController::class, 'analyze'])->name('api.price-analysis.analyze');
    });
});

require __DIR__.'/webhooks.php';
require __DIR__.'/mock.php';
