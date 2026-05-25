<?php

declare(strict_types=1);

namespace App\Modules\Shared\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Infrastructure\Jobs\SyncInventoryJob;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\Product\Infrastructure\Jobs\SyncProductsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Throwable;

class SyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $organizationId = $request->user()?->organization_id) {
            return response()->json(['message' => 'Unauthorized or organization ID not found.'], 401);
        }

        Bus::chain([
            new SyncProductsJob($organizationId),
            new SyncOrdersJob($organizationId),
            new SyncInventoryJob($organizationId),
        ])->catch(function (Throwable $e): void {
            report($e);
        })->dispatch();

        return response()->json(['message' => 'Sync jobs dispatched successfully.'], 200);
    }
}
