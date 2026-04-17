<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Actions;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\Domain\Events\ProductsSynced;
use App\Modules\Product\Domain\Models\Category;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class SaveMarketplaceProductsAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * @param  iterable<ProductData>  $products
     *
     * @throws Throwable
     */
    public function execute(MarketplaceConnection $connection, iterable $products): void
    {
        DB::transaction(function () use ($connection, $products): void {
            foreach ($products as $productData) {
                $this->saveProduct($connection, $productData);
            }
        });

        event(new ProductsSynced($connection->organization_id));
    }

    private function saveProduct(MarketplaceConnection $connection, ProductData $productData): void
    {
        // 1. Обработка категории
        $category = null;
        if ($productData->category) {
            $category = Category::firstOrCreate(['name' => $productData->category]);
        }

        // 2. Поиск или создание мастер-товара (по SKU/VendorCode)
        $product = $this->productRepository->findBySku($productData->vendor_code);

        $productAttributes = [
            'name' => $productData->name,
            'images' => $productData->images,
            'attributes' => $productData->attributes,
            'category_id' => $category?->id,
        ];

        if ($product instanceof Product) {
            $this->productRepository->updateProduct($product, $productAttributes);
        } else {
            $product = $this->productRepository->createProduct([
                'organization_id' => $connection->organization_id,
                'sku' => $productData->vendor_code,
                ...$productAttributes,
            ]);
        }

        // 3. Поиск или создание листинга (по External ID маркетплейса)
        $listing = $this->productRepository->findListingByExternalId($connection->marketplace, $productData->external_id);

        $listingData = [
            'product_id' => $product->id,
            'vendor_code' => $productData->vendor_code,
            'price' => $productData->price,
            'old_price' => $productData->old_price,
            'last_synced_at' => now(),
        ];

        if ($listing instanceof ProductListing) {
            $this->productRepository->updateListing($listing, $listingData);
        } else {
            $this->productRepository->createListing([
                'marketplace' => $connection->marketplace,
                'external_id' => $productData->external_id,
                'status' => 'active',
                ...$listingData,
            ]);
        }
    }
}
