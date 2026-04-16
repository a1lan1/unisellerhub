<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Application\Services;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockItemDetailsDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockItemDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockAssortmentDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockProductDetailsDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockProductListDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockProductDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockProductDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockStockDTO;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Repositories\MockOrderRepositoryInterface;
use App\Modules\MockMarketplace\Domain\Repositories\MockProductRepositoryInterface;
use App\Modules\MockMarketplace\Domain\Repositories\MockStockRepositoryInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class MockMarketplaceService
{
    public function __construct(
        private readonly MockStockRepositoryInterface $stockRepository,
        private readonly MockOrderRepositoryInterface $orderRepository,
        private readonly MockProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @return Collection<int, WbMockStockDTO>
     */
    public function getWbStocks(int $accountId, ?int $warehouseId = null): Collection
    {
        $stocks = $this->stockRepository->getStocks($accountId, MarketplaceEnum::WB, $warehouseId);

        return $stocks->map(fn ($s): WbMockStockDTO => new WbMockStockDTO(
            sku: (string) $s->sku,
            amount: (int) $s->quantity,
            warehouseId: (int) $s->external_warehouse_id,
        ));
    }

    public function updateWbStocks(int $accountId, string $warehouseId, array $stocks): void
    {
        foreach ($stocks as $s) {
            $this->stockRepository->updateQuantity(
                $accountId,
                $warehouseId,
                (string) $s['sku'],
                (int) $s['amount']
            );
        }
    }

    /**
     * @return Collection<int, WbMockOrderDTO>
     */
    public function getWbOrders(int $accountId): Collection
    {
        $orders = $this->orderRepository->getOrders($accountId, MarketplaceEnum::WB);

        return $orders->map(fn ($order): WbMockOrderDTO => new WbMockOrderDTO(
            id: (int) $order->external_order_id,
            status: (string) $order->status->value,
            createdAt: $order->order_date->toIso8601String(),
            price: (int) $order->total_price, // WB uses kopeks (int)
            items: collect($order->items)->map(fn ($item): array => [
                'nmId' => (string) ($item['product_id'] ?? ''),
                'sku' => (string) ($item['sku'] ?? ''),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (int) ($item['price'] ?? 0), // WB uses kopeks (int)
            ])->all(),
        ));
    }

    /**
     * @return Collection<int, WbMockProductDTO>
     */
    public function getWbProducts(int $accountId): Collection
    {
        $products = $this->productRepository->getProducts($accountId, MarketplaceEnum::WB);

        return $products->map(fn ($p): WbMockProductDTO => new WbMockProductDTO(
            nmId: (int) $p->external_id,
            vendorCode: (string) $p->vendor_code,
            title: (string) $p->name,
            description: (string) $p->description,
            brand: (string) $p->brand,
            photos: array_map(fn ($img): array => ['big' => (string) $img], $p->images ?? []),
            characteristics: $p->attributes,
            subjectName: (string) $p->category,
            sizes: [
                [
                    'skus' => [(string) $p->barcode],
                ],
            ],
            price: (int) $p->price, // WB uses kopeks (int)
        ));
    }

    public function updateWbPrices(int $accountId, array $prices): void
    {
        foreach ($prices as $p) {
            $this->productRepository->updatePrice(
                $accountId,
                (int) $p['nmId'],
                (float) $p['price']
            );
        }
    }

    /**
     * @return Collection<int, OzonMockProductListDTO>
     */
    public function getOzonProducts(int $accountId): Collection
    {
        $products = $this->productRepository->getProducts($accountId, MarketplaceEnum::OZON);

        return $products->map(fn ($p): OzonMockProductListDTO => new OzonMockProductListDTO(
            product_id: (int) $p->external_id,
            offer_id: (string) $p->vendor_code,
        ));
    }

    /**
     * @param  int[]  $productIds
     * @return Collection<int, OzonMockProductDetailsDTO>
     */
    public function getOzonProductDetails(int $accountId, array $productIds): Collection
    {
        $products = $this->productRepository->getProductsByIds($accountId, MarketplaceEnum::OZON, $productIds);

        return $products->map(fn ($p): OzonMockProductDetailsDTO => new OzonMockProductDetailsDTO(
            id: (int) $p->external_id,
            offer_id: (string) $p->vendor_code,
            name: (string) $p->name,
            price: number_format((float) $p->price, 2, '.', ''), // Ozon uses string rubles "1999.00"
            old_price: number_format((float) ($p->old_price ?? 0), 2, '.', ''),
            images: $p->images,
            barcode: (string) $p->barcode,
            category_id: 123,
            description: (string) $p->description,
            attributes: $p->attributes,
        ));
    }

    /**
     * @param  int[]  $productIds
     * @return Collection<int, OzonMockStockDTO>
     */
    public function getOzonStocks(int $accountId, array $productIds): Collection
    {
        $stocks = $this->stockRepository->getStocksByProductIds($accountId, MarketplaceEnum::OZON, $productIds);

        return $stocks->map(fn ($s): OzonMockStockDTO => new OzonMockStockDTO(
            product_id: (int) $s->external_product_id,
            offer_id: (string) $s->sku,
            stocks: [
                [
                    'warehouse_id' => (int) $s->external_warehouse_id ?: 456,
                    'present' => (int) $s->quantity,
                    'reserved' => (int) $s->reserved,
                ],
            ],
        ));
    }

    public function updateOzonStocks(int $accountId, array $stocks): void
    {
        foreach ($stocks as $s) {
            $this->stockRepository->updateOzonQuantity(
                $accountId,
                (string) $s['offer_id'],
                (int) $s['stock']
            );
        }
    }

    public function updateOzonPrices(int $accountId, array $prices): void
    {
        foreach ($prices as $p) {
            $this->productRepository->updateOzonPrice(
                $accountId,
                (string) $p['offer_id'],
                (float) $p['price']
            );
        }
    }

    /**
     * @return Collection<int, OzonMockOrderDTO>
     */
    public function getOzonOrders(int $accountId): Collection
    {
        $orders = $this->orderRepository->getOrders($accountId, MarketplaceEnum::OZON);

        return $orders->map(fn ($o): OzonMockOrderDTO => new OzonMockOrderDTO(
            posting_number: (string) $o->external_order_id,
            status: (string) $o->status->value,
            in_process_at: $o->order_date->toIso8601String(),
            products: array_map(fn (array $item): array => [
                'sku' => (int) ($item['product_id'] ?? 0),
                'offer_id' => (string) ($item['sku'] ?? ''),
                'quantity' => (int) $item['quantity'],
                'price' => number_format((float) ($item['price'] ?? 0), 2, '.', ''), // Ozon string rubles
            ], $o->items),
        ));
    }

    public function getYandexCampaigns(): array
    {
        return [
            [
                'id' => 12345,
                'name' => 'Mock Yandex Shop',
                'domain' => 'mock-shop.yandex.ru',
                'businessId' => 67890,
                'status' => 'ACTIVE',
            ],
        ];
    }

    /**
     * @return Collection<int, YandexMockProductDTO>
     */
    public function getYandexProducts(int $accountId): Collection
    {
        $products = $this->productRepository->getProducts($accountId, MarketplaceEnum::YANDEX);

        return $products->map(fn ($p): YandexMockProductDTO => new YandexMockProductDTO(
            offer: [
                'offerId' => (string) $p->vendor_code,
                'name' => (string) $p->name,
                'category' => (string) $p->category,
                'pictures' => $p->images,
                'vendor' => (string) $p->brand,
                'description' => (string) $p->description,
                'price' => (int) $p->price, // Yandex uses kopeks (int)
            ],
            mapping: [
                'marketSku' => (int) $p->external_id,
            ],
        ));
    }

    /**
     * @return Collection<int, YandexMockStockDTO>
     */
    public function getYandexStocks(int $accountId): Collection
    {
        $stocks = $this->stockRepository->getStocks($accountId, MarketplaceEnum::YANDEX);

        return $stocks->map(fn ($s): YandexMockStockDTO => new YandexMockStockDTO(
            offerId: (string) $s->sku,
            warehouseStocks: [
                [
                    'warehouseId' => (int) $s->external_warehouse_id ?: 123,
                    'count' => (int) $s->quantity,
                    'updatedAt' => $s->updated_at->toIso8601String(),
                ],
            ],
        ));
    }

    public function updateYandexStocks(int $accountId, array $skus): void
    {
        foreach ($skus as $s) {
            $this->stockRepository->updateYandexQuantity(
                $accountId,
                (string) $s['sku'],
                (int) ($s['warehouseStocks'][0]['count'] ?? 0)
            );
        }
    }

    /**
     * @return Collection<int, YandexMockOrderDTO>
     */
    public function getYandexOrders(int $accountId): Collection
    {
        $orders = $this->orderRepository->getOrders($accountId, MarketplaceEnum::YANDEX);

        return $orders->map(fn ($o): YandexMockOrderDTO => new YandexMockOrderDTO(
            id: (int) $o->external_order_id,
            status: (string) $o->status->value,
            creationDate: $o->order_date->toIso8601String(),
            items: array_map(fn (array $item): array => [
                'offerId' => (string) ($item['sku'] ?? ''),
                'sku' => (string) ($item['product_id'] ?? ''),
                'count' => (int) ($item['quantity'] ?? 1),
                'price' => (int) ($item['price'] ?? 0), // Yandex kopeks (int)
            ], $o->items),
            totalPrice: (int) $o->total_price, // Yandex kopeks (int)
        ));
    }

    public function updateYandexPrices(int $accountId, array $offers): void
    {
        foreach ($offers as $offer) {
            $this->productRepository->updateYandexPrice(
                $accountId,
                (string) $offer['offerId'],
                (float) $offer['price']['value']
            );
        }
    }

    /**
     * @return Collection<int, AvitoMockItemDTO>
     */
    public function getAvitoItems(int $accountId): Collection
    {
        $products = $this->productRepository->getProducts($accountId, MarketplaceEnum::AVITO);

        return $products->map(fn ($p): AvitoMockItemDTO => new AvitoMockItemDTO(
            id: (int) $p->external_id,
            title: (string) $p->name,
            price: number_format((float) $p->price, 2, '.', ''), // Avito string rubles
            status: 'ACTIVE',
            url: 'https://avito.ru/item/'.$p->external_id,
        ));
    }

    public function getAvitoItemDetails(int $accountId, int $itemId): AvitoMockItemDetailsDTO
    {
        $product = $this->productRepository->findProduct($accountId, MarketplaceEnum::AVITO, $itemId);

        if (! $product instanceof MockProduct) {
            throw new RuntimeException('Product not found');
        }

        return new AvitoMockItemDetailsDTO(
            id: (int) $product->external_id,
            title: (string) $product->name,
            description: (string) $product->description,
            price: number_format((float) $product->price, 2, '.', ''), // Avito string rubles
            status: 'ACTIVE',
            images: $product->images,
        );
    }

    /**
     * @return Collection<int, AvitoMockStockDTO>
     */
    public function getAvitoStocks(int $accountId): Collection
    {
        $stocks = $this->stockRepository->getStocks($accountId, MarketplaceEnum::AVITO);

        return $stocks->map(fn ($s): AvitoMockStockDTO => new AvitoMockStockDTO(
            item_id: (int) $s->external_product_id,
            quantity: (int) $s->quantity,
            warehouse_id: (string) ($s->external_warehouse_id ?: 'avito_wh'),
        ));
    }

    /**
     * @return Collection<int, AvitoMockOrderDTO>
     */
    public function getAvitoOrders(int $accountId): Collection
    {
        $orders = $this->orderRepository->getOrders($accountId, MarketplaceEnum::AVITO);

        return $orders->map(fn ($o): AvitoMockOrderDTO => new AvitoMockOrderDTO(
            id: (string) $o->external_order_id,
            status: (string) $o->status->value,
            createdAt: $o->order_date->toIso8601String(),
            totalPrice: number_format((float) $o->total_price, 2, '.', ''), // Avito string rubles
            items: array_map(fn (array $item): array => [
                'id' => (string) ($item['product_id'] ?? ''),
                'title' => 'Avito Product '.($item['product_id'] ?? ''),
                'price' => number_format((float) ($item['price'] ?? 0), 2, '.', ''), // Avito string rubles
                'count' => (int) $item['quantity'],
            ], $o->items),
        ));
    }

    public function updateAvitoPrice(int $accountId, int $itemId, float $price): void
    {
        $this->productRepository->updateAvitoPrice($accountId, $itemId, $price);
    }

    public function getAvitoSelf(): array
    {
        return [
            'id' => 123456,
            'name' => 'Mock Avito Seller',
            'email' => 'mock@avito.ru',
        ];
    }

    /**
     * @return Collection<int, MsMockAssortmentDTO>
     */
    public function getMsAssortment(int $accountId): Collection
    {
        $products = $this->productRepository->getProducts($accountId, MarketplaceEnum::MOYSKLAD);

        return $products->map(fn ($p): MsMockAssortmentDTO => new MsMockAssortmentDTO(
            meta: [
                'href' => 'https://api.moysklad.ru/api/remap/1.2/entity/product/'.$p->external_id,
                'type' => 'product',
            ],
            id: (string) $p->external_id,
            name: (string) $p->name,
            code: (string) $p->vendor_code,
            externalCode: (string) $p->external_id,
            article: (string) $p->vendor_code,
            salePrices: [
                ['value' => (int) $p->price, 'priceType' => ['name' => 'Цена продажи']], // MS kopeks (int)
            ],
            barcodes: [['ean13' => (string) $p->barcode]],
        ));
    }

    /**
     * @return Collection<int, MsMockStockDTO>
     */
    public function getMsStocks(int $accountId): Collection
    {
        $stocks = $this->stockRepository->getMsStocks($accountId);

        return $stocks->map(fn ($s): MsMockStockDTO => new MsMockStockDTO(
            stock: (float) $s->quantity,
            reserve: (float) $s->reserved,
            quantity: (float) ($s->quantity - $s->reserved),
            name: 'Product Name',
            article: (string) $s->sku,
        ));
    }

    /**
     * @return Collection<int, MsMockOrderDTO>
     */
    public function getMsOrders(int $accountId): Collection
    {
        $orders = $this->orderRepository->getMsOrders($accountId);

        return $orders->map(fn ($o): MsMockOrderDTO => new MsMockOrderDTO(
            id: (string) $o->external_order_id,
            name: 'Order '.$o->external_order_id,
            moment: $o->order_date->format('Y-m-d H:i:s'),
            sum: (int) $o->total_price, // MS kopeks (int)
            state: ['name' => (string) $o->status->value],
        ));
    }
}
