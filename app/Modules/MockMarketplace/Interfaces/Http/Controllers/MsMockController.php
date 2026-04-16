<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MockMarketplace\Application\Services\MockMarketplaceService;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ms\GetMsAssortmentRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ms\GetMsOrdersRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ms\GetMsStocksRequest;
use Illuminate\Http\JsonResponse;

class MsMockController extends Controller
{
    public function __construct(
        private readonly MockMarketplaceService $service
    ) {}

    /**
     * GET /entity/assortment
     */
    public function getAssortment(GetMsAssortmentRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $products = $this->service->getMsAssortment($accountId);

        return response()->json([
            'meta' => ['size' => $products->count()],
            'rows' => $products->map(fn ($product): array => $product->toArray())->all(),
        ]);
    }

    /**
     * GET /report/stock/all
     */
    public function getStocks(GetMsStocksRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $stocks = $this->service->getMsStocks($accountId);

        return response()->json([
            'rows' => $stocks->map(fn ($stock): array => $stock->toArray())->all(),
        ]);
    }

    /**
     * GET /entity/customerorder
     */
    public function getOrders(GetMsOrdersRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $orders = $this->service->getMsOrders($accountId);

        return response()->json([
            'rows' => $orders->map(fn ($order): array => $order->toArray())->all(),
        ]);
    }
}
