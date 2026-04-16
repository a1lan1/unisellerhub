<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MockMarketplace\Application\Services\MockMarketplaceService;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Avito\GetAvitoItemsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Avito\UpdateAvitoPriceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvitoMockController extends Controller
{
    public function __construct(
        private readonly MockMarketplaceService $service
    ) {}

    /**
     * GET /items/v2/list
     */
    public function getItems(GetAvitoItemsRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $products = $this->service->getAvitoItems($accountId);

        return response()->json([
            'resources' => $products->map(fn ($product): array => $product->toArray())->all(),
            'pagination' => [
                'total' => $products->count(),
            ],
        ]);
    }

    /**
     * GET /items/v2/item/{itemId}
     */
    public function getItem(Request $request, int $itemId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $product = $this->service->getAvitoItemDetails($accountId, $itemId);

        return response()->json($product->toArray());
    }

    /**
     * GET /items/v1/stocks (Mock-only endpoint for pulling stocks)
     */
    public function getStocks(Request $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $stocks = $this->service->getAvitoStocks($accountId);

        return response()->json([
            'stocks' => $stocks->map(fn ($stock): array => $stock->toArray())->all(),
        ]);
    }

    /**
     * GET /order/v1/list (Mock implementation)
     */
    public function getOrders(Request $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $orders = $this->service->getAvitoOrders($accountId);

        return response()->json([
            'orders' => $orders->map(fn ($order): array => $order->toArray())->all(),
        ]);
    }

    /**
     * PUT /items/v1/item/{itemId}/price
     */
    public function updatePrice(UpdateAvitoPriceRequest $request, int $itemId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $price = (float) $request->input('price');

        $this->service->updateAvitoPrice($accountId, $itemId, $price);

        return response()->json([
            'status' => 'success',
            'price' => $price,
        ]);
    }

    /**
     * GET /core/v1/accounts/self
     */
    public function getSelf(): JsonResponse
    {
        return response()->json($this->service->getAvitoSelf());
    }
}
