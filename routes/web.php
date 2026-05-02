<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Modules\Inventory\Interfaces\Http\Controllers\InventoryController;
use App\Modules\Order\Interfaces\Http\Controllers\OrderController;
use App\Modules\Product\Interfaces\Http\Controllers\ProductController;
use App\Modules\Report\Interfaces\Http\Controllers\AnalyticsController;
use App\Modules\Report\Interfaces\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
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

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function (): void {
        Route::get('/abc', [AnalyticsController::class, 'abc'])->name('abc');
        Route::get('/profitability', [AnalyticsController::class, 'profitability'])->name('profitability');
    });

    // Exports
    Route::prefix('exports')->name('exports.')->group(function (): void {
        Route::get('/orders', [ExportController::class, 'orders'])->name('orders');
        Route::get('/inventory', [ExportController::class, 'inventory'])->name('inventory');
    });
});

require __DIR__.'/settings.php';
