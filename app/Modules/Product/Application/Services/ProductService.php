<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Services;

use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\SyncBulkProductData;
use App\Modules\Product\Domain\Data\SyncProductsData;
use App\Modules\Product\Domain\Interfaces\ProductServiceInterface;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\Infrastructure\Jobs\SyncBulkProductsJob;
use App\Modules\Product\Infrastructure\Jobs\SyncProductsJob;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class ProductService implements ProductServiceInterface
{
    public function __construct(
        private ProductListingRepositoryInterface $productListingRepository
    ) {}

    public function getPaginatedListings(User $user, ProductListingsFilterData $filter): LengthAwarePaginator
    {
        if (! $user->has_organization) {
            return new LengthAwarePaginator([], 0, $filter->pagination->getPerPage());
        }

        return $this->productListingRepository->getPaginatedListings($filter);
    }

    public function syncProducts(SyncProductsData $dto): void
    {
        dispatch(new SyncProductsJob($dto->organizationId, $dto->marketplace));
    }

    public function syncBulkProducts(SyncBulkProductData $dto): void
    {
        SyncBulkProductsJob::dispatchIf(
            $dto->ids !== [],
            $dto->organizationId, $dto->ids
        );
    }
}
