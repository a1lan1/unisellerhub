<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Modules\Inventory\Interfaces\Http\Controllers\InventoryController;
use App\Modules\Marketplace\Interfaces\Http\Controllers\MarketplaceController;
use App\Modules\Order\Interfaces\Http\Controllers\OrderController;
use App\Modules\Product\Interfaces\Http\Controllers\ProductController;
use App\Modules\Report\Interfaces\Http\Controllers\AnalyticsController;
use App\Modules\Report\Interfaces\Http\Controllers\DownloadExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

    // Inventory
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Marketplaces
    Route::prefix('marketplaces/{marketplaceConnection}')->name('marketplaces.')->group(function (): void {
        Route::get('/', [MarketplaceController::class, 'show'])->name('show');
        Route::get('/logs', [MarketplaceController::class, 'logs'])->name('logs');
        Route::get('/messenger', [MarketplaceController::class, 'messenger'])->name('messenger');
    });

    Route::prefix('geo')->name('geo.')->group(function (): void {
        Route::get('dashboard', fn () => Inertia::render('geo/Dashboard'))->name('dashboard');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function (): void {
        Route::get('/abc', [AnalyticsController::class, 'abc'])->name('abc');
        Route::get('/profitability', [AnalyticsController::class, 'profitability'])->name('profitability');
    });

    // Download Exported File
    Route::get('/exports/download/{path}', DownloadExportController::class)
        ->name('exports.download')
        ->middleware('signed')
        ->where('path', '.*');
});

require __DIR__.'/settings.php';
