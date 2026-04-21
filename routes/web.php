<?php

declare(strict_types=1);

use App\Modules\Product\Interfaces\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    // Products
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
});

require __DIR__.'/settings.php';
