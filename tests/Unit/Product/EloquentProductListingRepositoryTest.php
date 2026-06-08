<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Data\ProductListingStoreData;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Infrastructure\Repositories\EloquentProductListingRepository;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\User\Domain\Models\Organization;
use Carbon\CarbonImmutable;
use Cknow\Money\Money;

beforeEach(function (): void {
    $this->repository = new EloquentProductListingRepository;
    $this->organization = Organization::factory()->create();
    $this->product = Product::factory()->create(['organization_id' => $this->organization->id]);
    resolve(TenantManager::class)->setOrganizationId($this->organization->id);
});

it('gets product listings by organization, marketplaces, and vendor code', function (): void {
    $listing1 = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'vendor_code' => 'VC001',
        'product_id' => $this->product->id,
    ]);
    $listing2 = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::WB,
        'vendor_code' => 'VC001',
        'product_id' => $this->product->id,
    ]);
    ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'vendor_code' => 'VC002',
        'product_id' => $this->product->id,
    ]);
    ProductListing::factory()->create();

    $listings = $this->repository->getByOrganizationAndMarketplaces(
        $this->organization->id,
        [MarketplaceEnum::OZON, MarketplaceEnum::WB],
        'VC001'
    );

    expect($listings)->toHaveCount(2);
    expect($listings->pluck('id'))->toContain($listing1->id, $listing2->id);
});

it('gets product listings by organization, marketplaces, and SKUs', function (): void {
    $listing1 = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'vendor_code' => 'SKU001',
        'product_id' => $this->product->id,
    ]);
    $listing2 = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::WB,
        'vendor_code' => 'SKU002',
        'product_id' => $this->product->id,
    ]);
    ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'vendor_code' => 'SKU003',
        'product_id' => $this->product->id,
    ]);

    $listings = $this->repository->getByOrganizationMarketplacesAndSkus(
        $this->organization->id,
        [MarketplaceEnum::OZON, MarketplaceEnum::WB],
        ['SKU001', 'SKU002']
    );

    expect($listings)->toHaveCount(2);
    expect($listings->pluck('id'))->toContain($listing1->id, $listing2->id);
});

it('gets product listings by IDs and organization', function (): void {
    $listing1 = ProductListing::factory()->create(['product_id' => $this->product->id]);
    $listing2 = ProductListing::factory()->create(['product_id' => $this->product->id]);
    ProductListing::factory()->create();

    $listings = $this->repository->getByIdsAndOrganization(
        [$listing1->id, $listing2->id],
        $this->organization->id
    );

    expect($listings)->toHaveCount(2);
    expect($listings->pluck('id'))->toContain($listing1->id, $listing2->id);
});

it('gets paginated listings with filters', function (): void {
    ProductListing::factory()->count(5)->create([
        'marketplace' => MarketplaceEnum::OZON,
        'product_id' => $this->product->id,
    ]);
    ProductListing::factory()->count(3)->create([
        'marketplace' => MarketplaceEnum::WB,
        'product_id' => $this->product->id,
    ]);

    $filter = ProductListingsFilterData::from([
        'organizationId' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'pagination' => new Pagination(perPage: 10, page: 1),
    ]);

    $paginator = $this->repository->getPaginatedListings($filter);

    expect($paginator->total())->toBe(5);
    expect($paginator->first()->marketplace)->toBe(MarketplaceEnum::OZON);
});

it('finds a listing by external ID and marketplace', function (): void {
    $listing = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'external_id' => 'ozon-ext-123',
        'product_id' => $this->product->id,
    ]);
    $foundListing = $this->repository->findListingByExternalId(MarketplaceEnum::OZON, 'ozon-ext-123');
    expect($foundListing->id)->toBe($listing->id);
});

it('returns null if listing not found by external ID and marketplace', function (): void {
    $foundListing = $this->repository->findListingByExternalId(MarketplaceEnum::OZON, 'non-existent');
    expect($foundListing)->toBeNull();
});

it('finds a listing by vendor code and marketplace', function (): void {
    $listing = ProductListing::factory()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'vendor_code' => 'VC-456',
        'product_id' => $this->product->id,
    ]);
    $foundListing = $this->repository->findListingByVendorCode(MarketplaceEnum::OZON, 'VC-456');
    expect($foundListing->id)->toBe($listing->id);
});

it('returns null if listing not found by vendor code and marketplace', function (): void {
    $foundListing = $this->repository->findListingByVendorCode(MarketplaceEnum::OZON, 'non-existent-vc');
    expect($foundListing)->toBeNull();
});

it('creates a new product listing', function (): void {
    $listingData = ProductListingStoreData::from([
        'productId' => $this->product->id,
        'marketplace' => MarketplaceEnum::OZON,
        'external_id' => 'new-ext-id',
        'vendor_code' => 'NEW-VC',
        'lastSyncedAt' => CarbonImmutable::now(),
        'price' => Money::RUB(10000),
        'old_price' => Money::RUB(12000),
        'discount' => 10,
    ]);

    $listing = $this->repository->updateOrCreate($listingData);

    expect($listing->external_id)->toBe('new-ext-id');
    expect($listing->vendor_code)->toBe('NEW-VC');
    $this->assertDatabaseHas('product_listings', ['external_id' => 'new-ext-id', 'marketplace' => MarketplaceEnum::OZON->value]);
});

it('updates an existing product listing', function (): void {
    $existingListing = ProductListing::factory()->create([
        'product_id' => $this->product->id,
        'marketplace' => MarketplaceEnum::OZON,
        'external_id' => 'existing-ext-id',
        'vendor_code' => 'OLD-VC',
        'price' => 5000,
    ]);

    $listingData = ProductListingStoreData::from([
        'productId' => $this->product->id,
        'marketplace' => MarketplaceEnum::OZON,
        'external_id' => 'existing-ext-id',
        'vendor_code' => 'UPDATED-VC',
        'lastSyncedAt' => CarbonImmutable::now(),
        'price' => Money::RUB(12000),
        'old_price' => Money::RUB(15000),
        'discount' => 15,
    ]);

    $listing = $this->repository->updateOrCreate($listingData);

    expect($listing->id)->toBe($existingListing->id);
    expect($listing->vendor_code)->toBe('UPDATED-VC');
    expect($listing->price->getAmount())->toBe('12000');
    $this->assertDatabaseHas('product_listings', ['id' => $existingListing->id, 'vendor_code' => 'UPDATED-VC', 'price' => 12000]);
});

it('updates a product listing', function (): void {
    $listing = ProductListing::factory()->create(['vendor_code' => 'Original VC', 'product_id' => $this->product->id]);
    $data = ['vendor_code' => 'Changed VC'];
    $updatedListing = $this->repository->updateListing($listing, $data);
    expect($updatedListing->vendor_code)->toBe('Changed VC');
    $this->assertDatabaseHas('product_listings', ['id' => $listing->id, 'vendor_code' => 'Changed VC']);
});

it('finds a listing by ID', function (): void {
    $listing = ProductListing::factory()->create(['product_id' => $this->product->id]);
    $foundListing = $this->repository->findListingById($listing->id);
    expect($foundListing->id)->toBe($listing->id);
    expect($foundListing->product)->not->toBeNull();
});

it('returns null if listing not found by ID', function (): void {
    $foundListing = $this->repository->findListingById(999);
    expect($foundListing)->toBeNull();
});

it('updates finance data for a product listing', function (): void {
    $listing = ProductListing::factory()->create([
        'product_id' => $this->product->id,
        'commission_percent' => 1000,
    ]);
    $this->product->update(['cost_price' => 5000]);

    $listingData = ['commission_percent' => 1500];
    $costPrice = Money::RUB(6000);

    $updatedListing = $this->repository->updateFinance($listing->id, $listingData, $costPrice);

    expect($updatedListing->id)->toBe($listing->id);
    expect($updatedListing->commission_percent->getValue())->toBe(1500.0);
    expect($updatedListing->product->cost_price->getAmount())->toBe('6000');
    $this->assertDatabaseHas('product_listings', ['id' => $listing->id, 'commission_percent' => 1500]);
    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'cost_price' => 6000]);
});

it('updates finance data for a product listing without cost price', function (): void {
    $listing = ProductListing::factory()->create([
        'product_id' => $this->product->id,
        'commission_percent' => 1000,
    ]);
    $this->product->update(['cost_price' => 5000]);

    $listingData = ['commission_percent' => 1500];

    $updatedListing = $this->repository->updateFinance($listing->id, $listingData, null);

    expect($updatedListing->id)->toBe($listing->id);
    expect($updatedListing->commission_percent->getValue())->toBe(1500.0);
    expect($updatedListing->product->cost_price->getAmount())->toBe('5000');
    $this->assertDatabaseHas('product_listings', ['id' => $listing->id, 'commission_percent' => 1500]);
    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'cost_price' => 5000]);
});
