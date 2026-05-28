<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Mappers;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Data\MarketplaceOrderItemData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Exceptions\InvalidMarketplaceDataException;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Shared\Infrastructure\Money\MoneyHelper;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class MarketplaceDataMapper
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
        $mappedStocks = [];
        foreach ($rawItems as $item) {
            try {
                $mappedStocks[] = $this->mapOneStock($marketplace, $item);
            } catch (InvalidMarketplaceDataException $e) {
                Log::warning(sprintf('Skipping stock item due to invalid data: %s', $e->getMessage()), [
                    'marketplace' => $marketplace->value,
                    'raw_item' => $e->rawData,
                    'exception' => $e->getTraceAsString(),
                ]);
            }
        }

        return $mappedStocks;
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

    /**
     * @throws InvalidMarketplaceDataException
     */
    private function mapOneStock(MarketplaceEnum $marketplace, array $item): StockData
    {
        $sku = '';
        $quantity = 0;
        $externalProductId = '';
        $externalWarehouseId = '';

        switch ($marketplace) {
            case MarketplaceEnum::WB:
                $externalProductId = (string) ($item['nmId'] ?? $item['sku'] ?? '');
                $externalWarehouseId = (string) (empty($item['warehouseName']) ? 'default' : $item['warehouseName']);
                $quantity = (int) ($item['amount'] ?? 0);
                $sku = (string) ($item['sku'] ?? '');
                break;
            case MarketplaceEnum::OZON:
                $externalProductId = (string) ($item['productId'] ?? '');
                $externalWarehouseId = 'ozon_wh'; // Default or derive from item
                $quantity = (int) ($item['stocks'][0]['present'] ?? 0);
                $sku = (string) ($item['offerId'] ?? '');
                break;
            case MarketplaceEnum::YANDEX:
                $externalProductId = (string) ($item['offerId'] ?? '');
                $externalWarehouseId = (string) ($item['warehouseStocks'][0]['warehouseId'] ?? 'yandex_wh');
                $quantity = (int) ($item['warehouseStocks'][0]['count'] ?? 0);
                $sku = (string) ($item['offerId'] ?? '');
                break;
            case MarketplaceEnum::AVITO:
                $externalProductId = (string) ($item['item_id'] ?? '');
                $externalWarehouseId = (string) ($item['warehouse_id'] ?? 'avito_wh');
                $quantity = (int) ($item['quantity'] ?? 0);
                $sku = (string) ($item['item_id'] ?? '');
                break;
            case MarketplaceEnum::MOYSKLAD:
                $externalProductId = (string) ($item['article'] ?? '');
                $externalWarehouseId = 'ms_wh'; // Default or derive from item
                $calculatedQuantity = (int) (($item['stock'] ?? 0) - ($item['reserve'] ?? 0));
                if ($calculatedQuantity < 0) {
                    throw new InvalidMarketplaceDataException(
                        sprintf('Calculated quantity is negative (%d) for MoySklad item.', $calculatedQuantity),
                        rawData: $item,
                        marketplace: $marketplace->value
                    );
                }

                $quantity = $calculatedQuantity;
                $sku = (string) ($item['article'] ?? '');
                break;
        }

        if ($externalProductId === '' || $externalProductId === '0') {
            throw new InvalidMarketplaceDataException(
                sprintf('External Product ID is empty for marketplace %s.', $marketplace->value),
                rawData: $item,
                marketplace: $marketplace->value
            );
        }

        return new StockData(
            external_product_id: new ExternalProductId($externalProductId),
            external_warehouse_id: new ExternalWarehouseId($externalWarehouseId),
            quantity: new Quantity($quantity),
            sku: $sku !== '' && $sku !== '0' ? new Sku($sku) : null,
        );
    }

    private function mapOneOrder(MarketplaceEnum $marketplace, array $item): OrderData
    {
        $items = array_map(function (array $i) use ($marketplace): MarketplaceOrderItemData {
            $skuValue = null;
            if (! empty($i['sku'])) {
                $skuValue = (string) $i['sku'];
            } elseif (! empty($i['offerId'])) {
                $skuValue = (string) $i['offerId'];
            }

            return new MarketplaceOrderItemData(
                product_id: new MarketplaceProductId((string) ($i['nmId'] ?? $i['sku'] ?? $i['productId'] ?? $i['id'] ?? '')),
                quantity: new Quantity((int) ($i['quantity'] ?? $i['count'] ?? 1)),
                price: MoneyHelper::fromMarketplace($i['price'] ?? 0, $marketplace),
                sku: $skuValue ? new Sku($skuValue) : null,
            );
        }, $item['items'] ?? $item['products'] ?? []);

        return new OrderData(
            external_id: new ExternalOrderId((string) ($item['id'] ?? $item['postingNumber'] ?? $item['article'] ?? '')),
            status: (string) ($item['status'] ?? $item['state']['name'] ?? 'new'),
            total_price: MoneyHelper::fromMarketplace($item['totalPrice'] ?? $item['price'] ?? $item['sum'] ?? 0, $marketplace),
            items: $items,
            order_date: Date::parse($item['createdAt'] ?? $item['inProcessAt'] ?? $item['creationDate'] ?? $item['moment'] ?? now()),
            delivery_info: $item['delivery_info'] ?? null,
        );
    }
}
