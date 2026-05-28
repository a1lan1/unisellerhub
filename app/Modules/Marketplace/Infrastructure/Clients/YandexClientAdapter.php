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

class YandexClientAdapter implements MarketplaceClientInterface
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
        protected ?string $campaignId = null,
        protected ?string $businessId = null,
        protected int $timeout = 30
    ) {}

    /**
     * @throws ConnectionException
     */
    public function getStocks(array $options = []): Collection
    {
        $businessId = $this->businessId ?? $options['business_id'] ?? null;
        if (! $businessId) {
            return collect();
        }

        $response = Http::withHeaders(['Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->post($this->baseUrl.sprintf('/v2/businesses/%s/offers/stocks', $this->businessId), [
                'limit' => $options['limit'] ?? 100,
            ]);

        if (! $response->successful() || ! isset($response->json()['result']['offers'])) {
            return collect();
        }

        return collect($response->json()['result']['offers'])->flatMap(fn ($item) => collect($item['warehouseStocks'])->map(fn ($stock): StockData => new StockData(
            external_product_id: new ExternalProductId((string) $item['offerId']),
            external_warehouse_id: new ExternalWarehouseId((string) $stock['warehouseId']),
            quantity: new Quantity((int) $stock['count']),
            sku: new Sku((string) $item['offerId'])
        )));
    }

    /**
     * @throws ConnectionException
     */
    public function getOrders(array $options = []): Collection
    {
        $campaignId = $this->campaignId ?? $options['campaign_id'] ?? null;
        if (! $campaignId) {
            return collect();
        }

        $response = Http::withHeaders(['Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->get($this->baseUrl.sprintf('/v2/campaigns/%s/orders', $campaignId), [
                'status' => $options['status'] ?? null,
                'limit' => $options['limit'] ?? 50,
            ]);

        if (! $response->successful() || ! isset($response->json()['result']['orders'])) {
            return collect();
        }

        return collect($response->json()['result']['orders'])->map(function (array $order): OrderData {
            $items = array_map(fn (array $i): MarketplaceOrderItemData => new MarketplaceOrderItemData(
                product_id: new MarketplaceProductId((string) ($i['sku'] ?? $i['offerId'])),
                quantity: new Quantity((int) $i['count']),
                price: MoneyHelper::fromMarketplace($i['price'] ?? 0, MarketplaceEnum::YANDEX),
                sku: new Sku((string) $i['offerId']),
            ), $order['items']);

            return new OrderData(
                external_id: new ExternalOrderId((string) $order['id']),
                status: (string) $order['status'],
                total_price: MoneyHelper::fromMarketplace($order['totalPrice'] ?? $order['total'] ?? 0, MarketplaceEnum::YANDEX),
                items: $items,
                order_date: new DateTime($order['creationDate']),
            );
        });
    }

    /**
     * @throws ConnectionException
     */
    public function getProducts(array $options = []): Collection
    {
        $businessId = $this->businessId ?? $options['business_id'] ?? null;
        if (! $businessId) {
            return collect();
        }

        $response = Http::withHeaders(['Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->post($this->baseUrl.sprintf('/v2/businesses/%s/offer-mappings', $this->businessId), [
                'limit' => $options['limit'] ?? 100,
            ]);

        if (! $response->successful() || ! isset($response->json()['result']['offers'])) {
            return collect();
        }

        return collect($response->json()['result']['offers'])->map(fn ($item): ProductData => new ProductData(
            external_id: (string) ($item['mapping']['marketSku'] ?? $item['offer']['offerId']),
            vendor_code: (string) $item['offer']['offerId'],
            name: (string) $item['offer']['name'],
            price: MoneyHelper::fromMarketplace($item['offer']['price'] ?? 0, MarketplaceEnum::YANDEX),
            images: $item['offer']['pictures'] ?? [],
            attributes: $item['offer']['attributes'] ?? [],
            brand: $item['offer']['vendor'] ?? null,
            category: $item['offer']['subjectName'] ?? null,
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function updatePrices(Collection $prices): bool
    {
        if (! $this->businessId) {
            return false;
        }

        $payload = [
            'offers' => $prices->map(fn ($p): array => [
                'offerId' => $p['sku'],
                'price' => [
                    'value' => (int) MoneyHelper::formatForApi($p['price'], MarketplaceEnum::YANDEX),
                    'currencyId' => 'RUR',
                ],
            ])->all(),
        ];

        return Http::withHeaders(['Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->post($this->baseUrl.sprintf('/v2/businesses/%s/offer-prices/updates', $this->businessId), $payload)
            ->successful();
    }

    /**
     * @throws ConnectionException
     */
    public function updateStocks(Collection $stocks): bool
    {
        if (! $this->businessId) {
            return false;
        }

        $payload = [
            'skus' => $stocks->map(fn ($s): array => [
                'sku' => $s->sku->getValue(),
                'warehouseStocks' => [
                    ['warehouseId' => (int) $s->external_warehouse_id->getValue(), 'count' => (int) $s->quantity->getValue()],
                ],
            ])->all(),
        ];

        return Http::withHeaders(['Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->post($this->baseUrl.sprintf('/v2/businesses/%s/offers/stocks', $this->businessId), $payload)
            ->successful();
    }
}
