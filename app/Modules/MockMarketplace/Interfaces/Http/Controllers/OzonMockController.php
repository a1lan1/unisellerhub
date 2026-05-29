<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MockMarketplace\Domain\Interfaces\MockMarketplaceServiceInterface;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\GetOzonOrdersRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\GetOzonProductDetailsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\GetOzonProductsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\GetOzonStocksRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\UpdateOzonPricesRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon\UpdateOzonStocksRequest;
use Illuminate\Http\JsonResponse;

class OzonMockController extends Controller
{
    public function __construct(
        private readonly MockMarketplaceServiceInterface $service
    ) {}

    /**
     * POST /v1/product/list
     * Получение списка идентификаторов товаров Ozon
     */
    public function getProducts(GetOzonProductsRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $products = $this->service->getOzonProducts($accountId);

        return response()->json([
            'result' => [
                'items' => $products->map(fn ($product): array => $product->toArray())->all(),
                'total' => $products->count(),
                'last_id' => '',
            ],
        ]);
    }

    /**
     * POST /v1/product/info/list
     * Получение детальной информации о товарах Ozon
     */
    public function getProductDetails(GetOzonProductDetailsRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $productIds = $request->input('product_id', []);

        $result = $this->service->getOzonProductDetails($accountId, $productIds);

        return response()->json([
            'result' => [
                'items' => $result->map(fn ($item): array => $item->toArray())->all(),
            ],
        ]);
    }

    /**
     * POST /v1/product/info/stocks
     * Получение остатков товаров Ozon
     */
    public function getStocks(GetOzonStocksRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $productIds = $request->input('product_id', []);

        $result = $this->service->getOzonStocks($accountId, $productIds);

        return response()->json([
            'result' => [
                'items' => $result->map(fn ($item): array => $item->toArray())->all(),
            ],
        ]);
    }

    /**
     * POST /v1/product/import/stocks
     * Обновление остатков товаров Ozon FBS
     */
    public function updateStocks(UpdateOzonStocksRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $this->service->updateOzonStocks($accountId, $request->input('stocks'));

        return response()->json(['result' => [['updated' => true]]]);
    }

    /**
     * POST /v1/product/import/prices
     * Обновление цен товаров Ozon
     */
    public function updatePrices(UpdateOzonPricesRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $this->service->updateOzonPrices($accountId, $request->input('prices'));

        return response()->json(['result' => [['updated' => true]]]);
    }

    /**
     * POST /v1/posting/fbs/list
     * Получение списка отправлений (заказов) Ozon FBS
     */
    public function getOrders(GetOzonOrdersRequest $request): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $orders = $this->service->getOzonOrders($accountId);

        return response()->json([
            'result' => [
                'postings' => $orders->map(fn ($order): array => $order->toArray())->all(),
            ],
        ]);
    }
}
