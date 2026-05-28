<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Clients;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Shared\Infrastructure\Money\MoneyHelper;
use DateTime;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MoySkladClientAdapter implements MarketplaceClientInterface
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
        $response = Http::withToken($this->token)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/api/remap/1.2/report/stock/all', $options);

        if (! $response->successful() || ! isset($response->json()['rows'])) {
            return collect();
        }

        return collect($response->json()['rows'])->map(fn ($item): StockData => new StockData(
            external_product_id: new ExternalProductId($item['article'] ?? ''),
            external_warehouse_id: new ExternalWarehouseId('ms_main'),
            quantity: new Quantity((int) $item['stock']),
            sku: new Sku($item['article'] ?? '')
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getOrders(array $options = []): Collection
    {
        $response = Http::withToken($this->token)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/api/remap/1.2/entity/customerorder', $options);

        if (! $response->successful() || ! isset($response->json()['rows'])) {
            return collect();
        }

        return collect($response->json()['rows'])->map(fn ($order): OrderData => new OrderData(
            external_id: new ExternalOrderId((string) $order['id']),
            status: $order['state']['name'] ?? 'unknown',
            total_price: MoneyHelper::fromMarketplace($order['sum'] ?? 0, MarketplaceEnum::MOYSKLAD),
            items: [],
            order_date: new DateTime($order['moment']),
        ));
    }

    /**
     * @throws ConnectionException
     */
    public function getProducts(array $options = []): Collection
    {
        $response = Http::withToken($this->token)
            ->timeout($this->timeout)
            ->get($this->baseUrl.'/api/remap/1.2/entity/assortment', $options);

        if (! $response->successful() || ! isset($response->json()['rows'])) {
            return collect();
        }

        return collect($response->json()['rows'])->map(fn ($item): ProductData => new ProductData(
            external_id: (string) $item['id'],
            vendor_code: $item['article'] ?? $item['code'] ?? '',
            name: $item['name'],
            price: MoneyHelper::fromMarketplace($item['salePrices'][0]['value'] ?? 0, MarketplaceEnum::MOYSKLAD),
            brand: null,
            category: null,
        ));
    }

    public function updatePrices(Collection $prices): bool
    {
        return true;
    }

    public function updateStocks(Collection $stocks): bool
    {
        // MS stock updates usually happen via documents (Enter/Loss).
        return true;
    }
}
