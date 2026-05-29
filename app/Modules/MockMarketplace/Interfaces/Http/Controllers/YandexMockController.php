<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MockMarketplace\Domain\Interfaces\MockMarketplaceServiceInterface;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex\GetYandexCampaignsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex\GetYandexOrdersRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex\GetYandexProductsRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex\UpdateYandexPricesRequest;
use App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex\UpdateYandexStocksRequest;
use Illuminate\Http\JsonResponse;

class YandexMockController extends Controller
{
    public function __construct(
        private readonly MockMarketplaceServiceInterface $service
    ) {}

    /**
     * GET /v2/campaigns
     */
    public function getCampaigns(GetYandexCampaignsRequest $request): JsonResponse
    {
        $campaigns = $this->service->getYandexCampaigns();

        return response()->json([
            'status' => 'OK',
            'result' => [
                'campaigns' => $campaigns,
            ],
        ]);
    }

    /**
     * POST /v2/businesses/{businessId}/offer-mappings
     */
    public function getProducts(GetYandexProductsRequest $request, int $businessId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $products = $this->service->getYandexProducts($accountId);

        return response()->json([
            'status' => 'OK',
            'result' => [
                'offers' => $products->map(fn ($product): array => $product->toArray())->all(),
                'paging' => ['nextPageToken' => null],
            ],
        ]);
    }

    /**
     * POST /v2/businesses/{businessId}/offers/stocks
     * Handle both GET (pull) and POST (update) simulation
     */
    public function updateStocks(UpdateYandexStocksRequest $request, int $businessId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $skus = $request->input('skus', []);

        // If 'skus' has count, it's an update (Push)
        if (! empty($skus) && isset($skus[0]['warehouseStocks'])) {
            $this->service->updateYandexStocks($accountId, $skus);

            return response()->json(['status' => 'OK', 'result' => ['status' => 'OK']]);
        }

        $stocks = $this->service->getYandexStocks($accountId);

        return response()->json([
            'status' => 'OK',
            'result' => [
                'offers' => $stocks->map(fn ($stock): array => $stock->toArray())->all(),
                'paging' => ['nextPageToken' => null],
            ],
        ]);
    }

    /**
     * GET /v2/campaigns/{campaignId}/orders
     */
    public function getOrders(GetYandexOrdersRequest $request, int $campaignId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');

        $orders = $this->service->getYandexOrders($accountId);

        return response()->json([
            'status' => 'OK',
            'result' => [
                'orders' => $orders->map(fn ($order): array => $order->toArray())->all(),
                'paging' => ['nextPageToken' => null],
            ],
        ]);
    }

    /**
     * POST /v2/businesses/{businessId}/offer-prices/updates
     */
    public function updatePrices(UpdateYandexPricesRequest $request, int $businessId): JsonResponse
    {
        $accountId = (int) $request->attributes->get('mock_account_id');
        $offers = $request->input('offers', []);

        $this->service->updateYandexPrices($accountId, $offers);

        return response()->json([
            'status' => 'OK',
            'result' => ['status' => 'OK'],
        ]);
    }
}
