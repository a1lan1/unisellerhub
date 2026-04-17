<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Mappers;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Shared\Infrastructure\Money\MoneyHelper;
use Illuminate\Support\Facades\Date;

final readonly class MarketplaceDataMapper
{
    /**
     * @param  array<string, mixed>  $rawItems
     * @return array<ProductData>
     */
    public function mapProducts(MarketplaceEnum $marketplace, array $rawItems): array
    {
        return array_map(fn (array $item): ProductData => $this->mapOneProduct($marketplace, $item), $rawItems);
    }

    /**
     * @param  array<string, mixed>  $rawItems
     * @return array<StockData>
     */
    public function mapStocks(MarketplaceEnum $marketplace, array $rawItems): array
    {
        return array_map(fn (array $item): StockData => $this->mapOneStock($marketplace, $item), $rawItems);
    }

    /**
     * @param  array<string, mixed>  $rawItems
     * @return array<OrderData>
     */
    public function mapOrders(MarketplaceEnum $marketplace, array $rawItems): array
    {
        return array_map(fn (array $item): OrderData => $this->mapOneOrder($marketplace, $item), $rawItems);
    }

    private function mapOneProduct(MarketplaceEnum $marketplace, array $item): ProductData
    {
        return match ($marketplace) {
            MarketplaceEnum::WB => new ProductData(
                external_id: (string) ($item['nmId'] ?? ''),
                vendor_code: (string) ($item['vendorCode'] ?? ''),
                name: (string) ($item['title'] ?? 'WB Product'),
                price: MoneyHelper::fromMarketplace($item['price'] ?? 0, $marketplace),
            ),
            MarketplaceEnum::OZON => new ProductData(
                external_id: (string) ($item['productId'] ?? ''),
                vendor_code: (string) ($item['offerId'] ?? ''),
                name: (string) ($item['name'] ?? 'Ozon Product'),
                price: MoneyHelper::fromMarketplace($item['price'] ?? 0, $marketplace),
            ),
            MarketplaceEnum::YANDEX => new ProductData(
                external_id: (string) ($item['offer']['offerId'] ?? ''),
                vendor_code: (string) ($item['offer']['offerId'] ?? ''),
                name: (string) ($item['offer']['name'] ?? 'Yandex Product'),
                price: MoneyHelper::fromMarketplace($item['offer']['price'] ?? 0, $marketplace),
            ),
            MarketplaceEnum::AVITO => new ProductData(
                external_id: (string) ($item['id'] ?? ''),
                vendor_code: (string) ($item['id'] ?? ''),
                name: (string) ($item['title'] ?? 'Avito Product'),
                price: MoneyHelper::fromMarketplace($item['price'] ?? 0, $marketplace),
            ),
            MarketplaceEnum::MOYSKLAD => new ProductData(
                external_id: (string) ($item['id'] ?? ''),
                vendor_code: (string) ($item['article'] ?? ''),
                name: (string) ($item['name'] ?? 'MS Product'),
                price: MoneyHelper::fromMarketplace($item['salePrices'][0]['value'] ?? 0, $marketplace),
            ),
        };
    }

    private function mapOneStock(MarketplaceEnum $marketplace, array $item): StockData
    {
        $data = match ($marketplace) {
            MarketplaceEnum::WB => [
                'external_product_id' => (string) ($item['nmId'] ?? ''),
                'external_warehouse_id' => (string) ($item['warehouseName'] ?? 'default'),
                'quantity' => (int) ($item['amount'] ?? 0),
                'sku' => (string) ($item['sku'] ?? ''),
            ],
            MarketplaceEnum::OZON => [
                'external_product_id' => (string) ($item['productId'] ?? ''),
                'external_warehouse_id' => 'ozon_wh',
                'quantity' => (int) ($item['stocks'][0]['present'] ?? 0),
                'sku' => (string) ($item['offerId'] ?? ''),
            ],
            MarketplaceEnum::YANDEX => [
                'external_product_id' => (string) ($item['offerId'] ?? ''),
                'external_warehouse_id' => (string) ($item['warehouseStocks'][0]['warehouseId'] ?? 'yandex_wh'),
                'quantity' => (int) ($item['warehouseStocks'][0]['count'] ?? 0),
                'sku' => (string) ($item['offerId'] ?? ''),
            ],
            MarketplaceEnum::AVITO => [
                'external_product_id' => (string) ($item['item_id'] ?? ''),
                'external_warehouse_id' => (string) ($item['warehouse_id'] ?? 'avito_wh'),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'sku' => (string) ($item['item_id'] ?? ''),
            ],
            MarketplaceEnum::MOYSKLAD => [
                'external_product_id' => (string) ($item['article'] ?? ''),
                'external_warehouse_id' => 'ms_wh',
                'quantity' => (int) (($item['stock'] ?? 0) - ($item['reserve'] ?? 0)),
                'sku' => (string) ($item['article'] ?? ''),
            ],
        };

        return StockData::from($data);
    }

    private function mapOneOrder(MarketplaceEnum $marketplace, array $item): OrderData
    {
        $items = array_map(fn (array $i): MarketplaceOrderItemData => new MarketplaceOrderItemData(
            product_id: (string) ($i['nmId'] ?? $i['sku'] ?? $i['productId'] ?? $i['id'] ?? ''),
            quantity: (int) ($i['quantity'] ?? $i['count'] ?? 1),
            price: MoneyHelper::fromMarketplace($i['price'] ?? 0, $marketplace),
            sku: (string) ($i['sku'] ?? $i['offerId'] ?? ''),
        ), $item['items'] ?? $item['products'] ?? []);

        return new OrderData(
            external_id: (string) ($item['id'] ?? $item['postingNumber'] ?? $item['article'] ?? ''),
            status: (string) ($item['status'] ?? $item['state']['name'] ?? 'new'),
            total_price: MoneyHelper::fromMarketplace($item['totalPrice'] ?? $item['price'] ?? $item['sum'] ?? 0, $marketplace),
            items: $items,
            order_date: Date::parse($item['createdAt'] ?? $item['inProcessAt'] ?? $item['creationDate'] ?? $item['moment'] ?? now()),
            delivery_info: $item['delivery_info'] ?? null,
        );
    }
}
