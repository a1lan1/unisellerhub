<?php

declare(strict_types=1);

use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\SyncBulkProductData;
use App\Modules\Product\Domain\Data\SyncProductsData;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\Infrastructure\Jobs\SyncBulkProductsJob;
use App\Modules\Product\Infrastructure\Jobs\SyncProductsJob;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Support\Facades\Bus;

beforeEach(function (): void {
    Bus::fake();
    $this->productListingRepository = $this->mock(ProductListingRepositoryInterface::class);
    $this->productService = new ProductService($this->productListingRepository);
});

it('returns empty paginator if user has no organization', function (): void {
    $user = User::factory()->make(['organization_id' => null]);
    $user->setRelation('organization', null);

    $filter = new ProductListingsFilterData(pagination: new Pagination(perPage: 15, page: 1));

    $this->productListingRepository->shouldNotReceive('getPaginatedListings');

    $paginator = $this->productService->getPaginatedListings($user, $filter);

    expect($paginator)->toBeInstanceOf(ConcreteLengthAwarePaginator::class);
    expect($paginator->total())->toBe(0);
    expect($paginator->perPage())->toBe(15);
});

it('gets paginated listings for a user with organization', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $organization = Organization::factory()->create();
    $user->organization_id = $organization->id;
    $user->setRelation('organization', $organization);

    $filter = new ProductListingsFilterData(pagination: new Pagination(perPage: 15, page: 1));
    $mockPaginator = new ConcreteLengthAwarePaginator([], 0, 15);

    $this->productListingRepository->shouldReceive('getPaginatedListings')
        ->once()
        ->with($filter)
        ->andReturn($mockPaginator);

    $paginator = $this->productService->getPaginatedListings($user, $filter);

    expect($paginator)->toEqual($mockPaginator);
});

it('dispatches SyncProductsJob', function (): void {
    $dto = new SyncProductsData(organizationId: 1);

    $this->productListingRepository->shouldNotReceive('syncProducts');

    $this->productService->syncProducts($dto);

    Bus::assertDispatched(
        SyncProductsJob::class,
        fn (SyncProductsJob $job): bool => $job->organizationId === $dto->organizationId && $job->marketplace === $dto->marketplace
    );
});

it('dispatches SyncBulkProductsJob if ids are not empty', function (): void {
    $dto = new SyncBulkProductData(organizationId: 1, ids: [1, 2, 3]);

    $this->productListingRepository->shouldNotReceive('syncBulkProducts');

    $this->productService->syncBulkProducts($dto);

    Bus::assertDispatched(
        SyncBulkProductsJob::class,
        fn (SyncBulkProductsJob $job): bool => $job->organizationId === $dto->organizationId && $job->listingIds === $dto->ids
    );
});

it('does not dispatch SyncBulkProductsJob if ids are empty', function (): void {
    $dto = new SyncBulkProductData(organizationId: 1, ids: []);

    $this->productListingRepository->shouldNotReceive('syncBulkProducts');

    $this->productService->syncBulkProducts($dto);

    Bus::assertNotDispatched(SyncBulkProductsJob::class);
});
