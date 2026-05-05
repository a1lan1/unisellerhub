<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Repositories;

use App\Modules\Product\Domain\Data\ProductStoreData;
use App\Modules\Product\Domain\Models\Product;

interface ProductRepositoryInterface
{
    public function updateOrCreate(ProductStoreData $productData): Product;
}
