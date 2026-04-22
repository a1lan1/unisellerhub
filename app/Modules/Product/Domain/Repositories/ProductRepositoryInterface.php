<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Product\Domain\Models\Product;

interface ProductRepositoryInterface
{
    public function findBySku(string $sku): ?Product;

    public function createProduct(array $data): Product;

    public function updateProduct(Product $product, array $data): Product;
}
