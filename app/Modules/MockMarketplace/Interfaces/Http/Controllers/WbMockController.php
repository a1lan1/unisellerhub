<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MockMarketplace\Domain\Interfaces\MockMarketplaceServiceInterface;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb\GetWbOrdersRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb\GetWbProductsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb\GetWbStocksRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb\UpdateWbPricesRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb\UpdateWbStocksRequest;
use Illuminate\Http\JsonResponse;

class WbMockController extends Controller
{
    public function __construct(
        private readonly MockMarketplaceServiceInterface $service
    ) {}

    /**
     * GET /api/v3/stocks
     * Получение остатков WB FBS
     */
    public function getStocks(GetWbStocksRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $warehouseId = $request->query('warehouseId') ? (int) $request->query('warehouseId') : null;

        $stocks = $this->service->getWbStocks($accountId, $warehouseId);

        return response()->json([
            'stocks' => $stocks->map(fn ($stock): array => $stock->toArray())->all(),
        ]);
    }

    /**
     * PUT /api/v3/stocks/{warehouseId}
     * Обновление остатков WB FBS
     */
    public function updateStocks(UpdateWbStocksRequest $request, string $warehouseId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $this->service->updateWbStocks($accountId, $warehouseId, $request->input('stocks'));

        return response()->json(['message' => 'Stocks updated']);
    }

    /**
     * GET /api/v3/orders
     * Получение новых сборочных заданий (заказов) WB
     */
    public function getOrders(GetWbOrdersRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $orders = $this->service->getWbOrders($accountId);

        return response()->json([
            'orders' => $orders->map(fn ($order): array => $order->toArray())->all(),
        ]);
    }

    /**
     * POST /content/v2/get/cards/list
     * Получение списка карточек товаров WB через API контента
     */
    public function getProducts(GetWbProductsRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $products = $this->service->getWbProducts($accountId);

        return response()->json([
            'cards' => $products->map(fn ($product): array => $product->toArray())->all(),
            'cursor' => [
                'updatedAt' => now()->toIso8601String(),
                'nmId' => 0,
                'total' => $products->count(),
            ],
        ]);
    }

    /**
     * POST /public/api/v1/prices
     * Обновление цен товаров WB
     */
    public function updatePrices(UpdateWbPricesRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $this->service->updateWbPrices($accountId, $request->all());

        return response()->json(['message' => 'Prices updated']);
    }
}
