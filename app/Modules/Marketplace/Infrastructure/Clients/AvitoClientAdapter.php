<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Clients;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Shared\Infrastructure\Money\MoneyHelper;
use DateTime;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AvitoClientAdapter implements MarketplaceClientInterface
{
    public function __construct(
        protected string $baseUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected int $timeout = 30
    ) {}

    /**
     * @throws ConnectionException
     */
    public function getStocks(array $options = []): Collection
    {
        // For Avito mock, we use client_id as a Bearer token
        $response = Http::withToken($this->clientId)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/items/v1/stocks', $options);

        if (! $response->successful() || ! isset($response->json()['stocks'])) {
            return collect();
        }

        return collect($response->json()['stocks'])->map(fn ($item): StockData => new StockData(
            external_product_id: (string) $item['item_id'],
            external_warehouse_id: (string) $item['warehouse_id'],
            quantity: (int) $item['quantity'],
            sku: (string) $item['item_id']
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getOrders(array $options = []): Collection
    {
        $response = Http::withToken($this->clientId)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/order/v1/list', $options);

        if (! $response->successful() || ! isset($response->json()['orders'])) {
            return collect();
        }

        return collect($response->json()['orders'])->map(fn ($order): OrderData => new OrderData(
            external_id: (string) $order['id'],
            status: (string) $order['status'],
            total_price: MoneyHelper::fromMarketplace($order['totalPrice'], MarketplaceEnum::AVITO),
            items: array_map(fn (array $item): MarketplaceOrderItemData => new MarketplaceOrderItemData(
                product_id: (string) $item['id'],
                quantity: (int) $item['count'],
                price: MoneyHelper::fromMarketplace($item['price'], MarketplaceEnum::AVITO),
            ), $order['items']),
            order_date: new DateTime($order['createdAt']),
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getProducts(array $options = []): Collection
    {
        $response = Http::withToken($this->clientId)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/items/v2/list', $options);

        if (! $response->successful() || ! isset($response->json()['resources'])) {
            return collect();
        }

        return collect($response->json()['resources'])->map(fn ($item): ProductData => new ProductData(
            external_id: (string) $item['id'],
            vendor_code: (string) $item['id'],
            name: (string) $item['title'],
            price: MoneyHelper::fromMarketplace($item['price'], MarketplaceEnum::AVITO),
            images: [],
            attributes: [],
            brand: null,
            category: null,
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function updatePrices(Collection $prices): bool
    {
        foreach ($prices as $p) {
            $response = Http::withToken($this->clientId)
                ->timeout($this->timeout)
                ->put($this->baseUrl.sprintf('/items/v1/item/%s/price', $p['external_id']), [
                    'price' => (int) $p['price'],
                ]);

            if (! $response->successful()) {
                return false;
            }
        }

        return true;
    }

    public function updateStocks(Collection $stocks): bool
    {
        // Avito stock update usually via Autoload XML orvas services.
        return true;
    }
}
