<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Repositories;

use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findBySku(string $sku): ?Product
    {
        return Product::query()
            ->where('sku', $sku)
            ->first();
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }
}
