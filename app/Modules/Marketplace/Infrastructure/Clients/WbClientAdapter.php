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

class WbClientAdapter implements MarketplaceClientInterface
{
    public function __construct(
        protected string $baseUrl,
        protected string $token,
        protected int $timeout = 30
    ) {}

    /**
     * @throws ConnectionException
     */
    public function getStocks(array $options = []): Collection
    {
        $response = Http::withHeader('Authorization', $this->token)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/api/v3/stocks', $options);

        if (! $response->successful() || ! isset($response->json()['stocks'])) {
            return collect();
        }

        return collect($response->json()['stocks'])->map(fn ($item): StockData => new StockData(
            external_product_id: (string) $item['sku'],
            external_warehouse_id: (string) $item['warehouseId'],
            quantity: $item['amount'],
            sku: (string) $item['sku']
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getOrders(array $options = []): Collection
    {
        $response = Http::withHeader('Authorization', $this->token)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/api/v3/orders', $options);

        if (! $response->successful() || ! isset($response->json()['orders'])) {
            return collect();
        }

        return collect($response->json()['orders'])->map(fn ($order): OrderData => new OrderData(
            external_id: (string) $order['id'],
            status: $order['status'],
            total_price: MoneyHelper::fromMarketplace($order['price'], MarketplaceEnum::WB),
            items: array_map(fn (array $item): MarketplaceOrderItemData => new MarketplaceOrderItemData(
                product_id: (string) ($item['nmId'] ?? ''),
                quantity: (int) ($item['quantity'] ?? 1),
                price: MoneyHelper::fromMarketplace($item['price'] ?? 0, MarketplaceEnum::WB),
                sku: (string) ($item['sku'] ?? ''),
            ), $order['items']),
            order_date: new DateTime($order['createdAt']),
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getProducts(array $options = []): Collection
    {
        $response = Http::withHeader('Authorization', $this->token)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/content/v2/get/cards/list', [
                'settings' => [
                    'cursor' => ['limit' => $options['limit'] ?? 100],
                    'filter' => ['withPhoto' => -1],
                ],
            ]);

        if (! $response->successful() || ! isset($response->json()['cards'])) {
            return collect();
        }

        return collect($response->json()['cards'])->map(fn ($card): ProductData => new ProductData(
            external_id: (string) $card['nmId'],
            vendor_code: $card['vendorCode'],
            name: $card['title'] ?? 'No name',
            price: MoneyHelper::fromMarketplace(0, MarketplaceEnum::WB),
            images: array_map(fn (array $img): string => $img['big'], $card['photos'] ?? []),
            attributes: $card['characteristics'] ?? [],
            brand: $card['brand'] ?? null,
            category: $card['subjectName'] ?? null,
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function updatePrices(Collection $prices): bool
    {
        $payload = $prices->map(fn ($p): array => [
            'nmId' => (int) $p['external_id'],
            'price' => (int) MoneyHelper::formatForApi($p['price'], MarketplaceEnum::WB),
        ])->all();

        return Http::withHeader('Authorization', $this->token)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/public/api/v1/prices', $payload)
            ->successful();
    }

    /**
     * @throws ConnectionException
     */
    public function updateStocks(Collection $stocks): bool
    {
        // WB requires warehouseId in URL
        $stocksByWarehouse = $stocks->groupBy('external_warehouse_id');

        foreach ($stocksByWarehouse as $warehouseId => $items) {
            $payload = ['stocks' => $items->map(fn ($s): array => ['sku' => $s->sku, 'amount' => $s->quantity])->all()];

            $response = Http::withHeader('Authorization', $this->token)
                ->timeout($this->timeout)
                ->put($this->baseUrl.('/api/v3/stocks/'.$warehouseId), $payload);

            if (! $response->successful()) {
                return false;
            }
        }

        return true;
    }
}
