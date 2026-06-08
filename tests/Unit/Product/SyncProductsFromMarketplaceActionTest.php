<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Interfaces\MarketplaceClientInterface;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\Application\Actions\SaveMarketplaceProductsAction;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Product\Domain\Data\ProductStoreData;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\User\Domain\Models\Organization;
use Cknow\Money\Money;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->productRepository = $this->mock(ProductRepositoryInterface::class);
    $this->productListingRepository = $this->mock(ProductListingRepositoryInterface::class);

    $this->saveProductsAction = new SaveMarketplaceProductsAction(
        $this->productRepository,
        $this->productListingRepository
    );

    $this->marketplaceClientFactory = Mockery::mock(new MarketplaceClientFactory);
    $this->action = new SyncProductsFromMarketplaceAction(
        $this->saveProductsAction,
        $this->marketplaceClientFactory
    );

    $this->organization = Organization::factory()->create();

    $this->connection = MarketplaceConnection::factory()->make([
        'marketplace' => MarketplaceEnum::OZON,
        'credentials' => ['token' => 'test_token'],
        'organization_id' => $this->organization->id,
    ]);
    $this->marketplaceClient = $this->mock(MarketplaceClientInterface::class);
});

it('syncs products from marketplace and saves them', function (): void {
    $products = new Collection([
        new ProductData(
            external_id: 'ext-prod-1',
            vendor_code: '123456789',
            name: 'Test Product',
            price: Money::RUB(1000),
        ),
    ]);

    $this->marketplaceClientFactory->shouldReceive('make')
        ->once()
        ->with($this->connection->marketplace, $this->connection->credentials)
        ->andReturn($this->marketplaceClient);

    $this->marketplaceClient->shouldReceive('getProducts')
        ->once()
        ->andReturn($products);

    $mockedProduct = Mockery::mock(Product::class)->makePartial();
    $mockedProduct->id = 1;

    $this->productRepository->shouldReceive('updateOrCreate')
        ->once()
        ->withArgs(fn (ProductStoreData $productStoreData): bool => $productStoreData->sku === '123456789' &&
            $productStoreData->name === 'Test Product' &&
            $productStoreData->organizationId === $this->connection->organization_id
        )
        ->andReturn($mockedProduct);

    $mockedProductListing = Mockery::mock(ProductListing::class)->makePartial();
    $mockedProductListing->id = 1;

    $this->productListingRepository->shouldReceive('updateOrCreate')
        ->once()
        ->withAnyArgs()
        ->andReturn($mockedProductListing);

    $this->action->execute($this->connection);
});
