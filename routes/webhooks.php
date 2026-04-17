<?php

declare(strict_types=1);

use App\Modules\Marketplace\Interfaces\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhook Endpoints
Route::prefix('webhooks')->group(function (): void {
    Route::post('/wb', [WebhookController::class, 'wildberries'])->name('webhooks.wb');
    Route::post('/ozon', [WebhookController::class, 'ozon'])->name('webhooks.ozon');
    Route::post('/ms', [WebhookController::class, 'moysklad'])->name('webhooks.ms');
    Route::post('/avito', [WebhookController::class, 'avito'])->name('webhooks.avito');
    Route::post('/yandex', [WebhookController::class, 'yandex'])->name('webhooks.yandex');
});
