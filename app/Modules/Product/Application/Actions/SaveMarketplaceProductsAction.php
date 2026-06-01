<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Actions;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\Domain\Data\ProductListingStoreData;
use App\Modules\Product\Domain\Data\ProductStoreData;
use App\Modules\Product\Domain\Events\ProductsSynced;
use App\Modules\Product\Domain\Models\Category;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveMarketplaceProductsAction
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductListingRepositoryInterface $productListingRepository
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
        // Prepare categity
        if ($productData->category) {
            $category = Category::firstOrCreate(['name' => $productData->category]);
            $productData->categoryId = $category->id;
        }

        // Store product
        $product = $this->productRepository->updateOrCreate(
            new ProductStoreData(
                sku: $productData->vendor_code,
                name: $productData->name,
                organizationId: $connection->organization_id,
                images: $productData->images,
                attributes: $productData->attributes,
                categoryId: $productData->categoryId,
            )
        );

        // Store product listing (by External ID marketplace)
        $this->productListingRepository->updateOrCreate(
            new ProductListingStoreData(
                productId: $product->id,
                marketplace: $connection->marketplace,
                external_id: $productData->external_id,
                vendor_code: $productData->vendor_code,
                lastSyncedAt: now(),
                price: $productData->price,
                old_price: $productData->old_price,
            )
        );
    }
}
