<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Modules\Inventory\Interfaces\Http\Controllers\InventoryController;
use App\Modules\Order\Interfaces\Http\Controllers\OrderController;
use App\Modules\Product\Interfaces\Http\Controllers\ProductController;
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
});

require __DIR__.'/settings.php';
