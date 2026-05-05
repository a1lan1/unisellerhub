<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Modules\Product\Domain\Data\ProductStoreData;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function updateOrCreate(ProductStoreData $productData): Product
    {
        return Product::updateOrCreate(
            ['sku' => $productData->sku],
            $productData->toArray()
        );
    }
}
