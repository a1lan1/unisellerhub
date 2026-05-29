<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Interfaces;

use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\SyncBulkProductData;
use App\Modules\Product\Domain\Data\SyncProductsData;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function getPaginatedListings(User $user, ProductListingsFilterData $filter): LengthAwarePaginator;

    public function syncProducts(SyncProductsData $dto): void;

    public function syncBulkProducts(SyncBulkProductData $dto): void;
}
