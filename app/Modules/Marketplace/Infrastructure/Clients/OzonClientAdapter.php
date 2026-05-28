<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Clients;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Shared\Infrastructure\Money\MoneyHelper;
use DateTime;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OzonClientAdapter implements MarketplaceClientInterface
{
    public function __construct(
        protected string $baseUrl,
        protected array $headers,
        protected int $timeout = 30
    ) {}

    /**
     * @throws ConnectionException
     */
    public function getStocks(array $options = []): Collection
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/product/info/stocks', [
                'offer_id' => $options['offer_ids'] ?? [],
                'product_id' => $options['product_ids'] ?? [],
            ]);

        if (! $response->successful() || ! isset($response->json()['result']['items'])) {
            return collect();
        }

        return collect($response->json()['result']['items'])->flatMap(fn ($item) => collect($item['stocks'])->map(fn ($stock): StockData => new StockData(
            external_product_id: new ExternalProductId((string) $item['product_id']),
            external_warehouse_id: new ExternalWarehouseId((string) $stock['warehouse_id']),
            quantity: new Quantity($stock['present']),
            sku: new Sku((string) $item['offer_id'])
        )));
    }

    /**
     * @throws ConnectionException
     */
    public function getOrders(array $options = []): Collection
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/posting/fbs/list', [
                'dir' => 'ASC',
                'filter' => [
                    'since' => $options['since'] ?? now()->subDays(7)->toIso8601String(),
                    'to' => $options['to'] ?? now()->toIso8601String(),
                ],
                'limit' => 50,
                'offset' => 0,
            ]);

        if (! $response->successful() || ! isset($response->json()['result']['postings'])) {
            return collect();
        }

        return collect($response->json()['result']['postings'])->map(function (array $order): OrderData {
            $items = array_map(fn (array $p): MarketplaceOrderItemData => new MarketplaceOrderItemData(
                product_id: new MarketplaceProductId((string) $p['sku']),
                quantity: new Quantity((int) $p['quantity']),
                price: MoneyHelper::fromMarketplace($p['price'], MarketplaceEnum::OZON),
                sku: ($p['offer_id'] ?? null) ? new Sku((string) $p['offer_id']) : null,
            ), $order['products']);

            $totalPrice = array_reduce($items, fn ($sum, MarketplaceOrderItemData $item) => $sum->add($item->price->multiply($item->quantity->getValue())), MoneyHelper::fromMarketplace(0, MarketplaceEnum::OZON));

            return new OrderData(
                external_id: new ExternalOrderId((string) $order['posting_number']),
                status: $order['status'],
                total_price: $totalPrice,
                items: $items,
                order_date: new DateTime($order['in_process_at']),
            );
        });
    }

    /**
     * @throws ConnectionException
     */
    public function getProducts(array $options = []): Collection
    {
        // 1. Get List
        $listResponse = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/product/list', [
                'limit' => $options['limit'] ?? 100,
                'last_id' => $options['last_id'] ?? '',
            ]);

        if (! $listResponse->successful() || ! isset($listResponse->json()['result']['items'])) {
            return collect();
        }

        $productIds = collect($listResponse->json()['result']['items'])->pluck('product_id')->toArray();
        if (empty($productIds)) {
            return collect();
        }

        // 2. Get Details
        $infoResponse = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/product/info/list', ['product_id' => $productIds]);

        if (! $infoResponse->successful() || ! isset($infoResponse->json()['result']['items'])) {
            return collect();
        }

        return collect($infoResponse->json()['result']['items'])->map(fn ($product): ProductData => new ProductData(
            external_id: (string) $product['id'],
            vendor_code: $product['offer_id'],
            name: $product['name'],
            price: MoneyHelper::fromMarketplace($product['price'], MarketplaceEnum::OZON),
            old_price: isset($product['old_price']) ? MoneyHelper::fromMarketplace($product['old_price'], MarketplaceEnum::OZON) : null,
            images: $product['images'] ?? [],
            attributes: $product['attributes'] ?? [],
            brand: $product['brand'] ?? null,
            category: $product['category'] ?? null,
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function updatePrices(Collection $prices): bool
    {
        $payload = ['prices' => $prices->map(fn ($p): array => [
            'offer_id' => $p['vendor_code'],
            'price' => MoneyHelper::formatForApi($p['price'], MarketplaceEnum::OZON),
        ])->all()];

        return Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/product/import/prices', $payload)
            ->successful();
    }

    /**
     * @throws ConnectionException
     */
    public function updateStocks(Collection $stocks): bool
    {
        $payload = ['stocks' => $stocks->map(fn ($s): array => ['offer_id' => $s->sku->getValue(), 'stock' => $s->quantity->getValue()])->all()];

        return Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post($this->baseUrl.'/v1/product/import/stocks', $payload)
            ->successful();
    }
}
