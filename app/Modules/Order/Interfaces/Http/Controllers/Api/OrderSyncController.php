<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Domain\Data\SyncOrdersData;
use App\Modules\Order\Interfaces\Http\Requests\SyncOrderRequest;
use Illuminate\Http\JsonResponse;

class OrderSyncController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function sync(SyncOrderRequest $request): JsonResponse
    {
        $marketplace = MarketplaceEnum::tryFrom((string) $request->input('marketplace'));

        $this->orderService->syncOrders(new SyncOrdersData(
            organizationId: $request->user()->organization_id,
            marketplace: $marketplace
        ));

        return response()->json(['message' => 'Orders sync job dispatched!']);
    }
}
