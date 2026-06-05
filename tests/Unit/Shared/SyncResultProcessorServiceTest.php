<?php

declare(strict_types=1);

use App\Modules\Inventory\Application\Actions\SaveMarketplaceStockAction;
use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Application\Mappers\MarketplaceDataMapper;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Order\Application\Actions\SaveMarketplaceOrdersAction;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Product\Application\Actions\SaveMarketplaceProductsAction;
use App\Modules\Product\Domain\Data\ProductData;
use App\Modules\Shared\Application\Services\SyncResultProcessorService;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Models\Organization;
use Carbon\CarbonImmutable;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Prometheus\Facades\Prometheus;
use Spatie\Prometheus\MetricTypes\Counter;

beforeEach(function (): void {
    Prometheus::clearResolvedInstances();

    $this->tenantManager = $this->mock(TenantManager::class)->makePartial();
    $this->connectionRepository = $this->mock(MarketplaceConnectionRepositoryInterface::class)->makePartial();
    $this->marketplaceDataMapper = $this->mock(MarketplaceDataMapper::class)->makePartial();
    $this->saveMarketplaceStockAction = $this->mock(SaveMarketplaceStockAction::class);
    $this->saveMarketplaceOrdersAction = $this->mock(SaveMarketplaceOrdersAction::class);
    $this->saveMarketplaceProductsAction = $this->mock(SaveMarketplaceProductsAction::class);

    $this->service = new SyncResultProcessorService(
        $this->tenantManager,
        $this->connectionRepository,
        $this->marketplaceDataMapper,
        $this->saveMarketplaceStockAction,
        $this->saveMarketplaceOrdersAction,
        $this->saveMarketplaceProductsAction
    );

    $this->organization = Organization::factory()->create();
    $this->connection = MarketplaceConnection::factory()->make([
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON,
    ]);
});

it('processes successful inventory sync result', function (): void {
    $data = [
        'operation' => 'inventory',
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'data' => [['external_id' => 'item1', 'quantity' => 10]],
        'duration' => 1.5,
    ];
    $stocks = new Collection([
        new StockData(
            external_product_id: new ExternalProductId('item1'),
            external_warehouse_id: new ExternalWarehouseId('default'),
            quantity: new Quantity(10)
        ),
    ]);

    $this->tenantManager->shouldReceive('setOrganizationId')
        ->once()
        ->with($this->organization->id);
    $this->connectionRepository->shouldReceive('findByOrganizationAndMarketplace')
        ->once()
        ->with($this->organization->id, MarketplaceEnum::OZON)
        ->andReturn($this->connection);
    $this->marketplaceDataMapper->shouldReceive('mapStocks')
        ->once()
        ->with(MarketplaceEnum::OZON, $data['data'])
        ->andReturn($stocks->toArray());
    $this->saveMarketplaceStockAction->shouldReceive('execute')
        ->once()
        ->with($this->connection, $stocks);

    Log::shouldReceive('info')->once()->withAnyArgs();

    // Mock Prometheus facade
    $mockCounterSum = Mockery::mock(Counter::class);
    $mockCounterSum->shouldReceive('inc')->once()->with(1.5, [MarketplaceEnum::OZON->value, 'inventory']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_sum')
        ->andReturn($mockCounterSum);

    $mockCounterCount = Mockery::mock(Counter::class);
    $mockCounterCount->shouldReceive('inc')->once()->with(1, [MarketplaceEnum::OZON->value, 'inventory']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_count')
        ->andReturn($mockCounterCount);

    $this->service->processSuccess($data);
});

it('processes successful orders sync result', function (): void {
    $data = [
        'operation' => 'orders',
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'data' => [['external_id' => 'order1', 'status' => 'new']],
        'duration' => 2.0,
    ];
    $orders = new Collection([
        new OrderData(
            external_id: new ExternalOrderId('order1'),
            status: 'new',
            total_price: Money::RUB(1000),
            items: [],
            order_date: CarbonImmutable::now(),
            delivery_info: [],
        ),
    ]);

    $this->tenantManager->shouldReceive('setOrganizationId')
        ->once()
        ->with($this->organization->id);
    $this->connectionRepository->shouldReceive('findByOrganizationAndMarketplace')
        ->once()
        ->with($this->organization->id, MarketplaceEnum::OZON)
        ->andReturn($this->connection);
    $this->marketplaceDataMapper->shouldReceive('mapOrders')
        ->once()
        ->with(MarketplaceEnum::OZON, $data['data'])
        ->andReturn($orders->toArray());
    $this->saveMarketplaceOrdersAction->shouldReceive('execute')
        ->once()
        ->with($this->connection, $orders);

    Log::shouldReceive('info')->once()->withAnyArgs();

    // Mock Prometheus facade
    $mockCounterSum = Mockery::mock(Counter::class);
    $mockCounterSum->shouldReceive('inc')->once()->with(2.0, [MarketplaceEnum::OZON->value, 'orders']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_sum')
        ->andReturn($mockCounterSum);

    $mockCounterCount = Mockery::mock(Counter::class);
    $mockCounterCount->shouldReceive('inc')->once()->with(1, [MarketplaceEnum::OZON->value, 'orders']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_count')
        ->andReturn($mockCounterCount);

    $this->service->processSuccess($data);
});

it('processes successful products sync result', function (): void {
    $data = [
        'operation' => 'products',
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'data' => [['external_id' => 'prod1', 'name' => 'Product 1']],
        'duration' => 3.0,
    ];
    $products = new Collection([
        new ProductData(
            external_id: 'prod1',
            vendor_code: '123456',
            name: 'Product 1',
            price: Money::RUB(1000),
        ),
    ]);

    $this->tenantManager->shouldReceive('setOrganizationId')
        ->once()
        ->with($this->organization->id);
    $this->connectionRepository->shouldReceive('findByOrganizationAndMarketplace')
        ->once()
        ->with($this->organization->id, MarketplaceEnum::OZON)
        ->andReturn($this->connection);
    $this->marketplaceDataMapper->shouldReceive('mapProducts')
        ->once()
        ->with(MarketplaceEnum::OZON, $data['data'])
        ->andReturn($products->toArray());
    $this->saveMarketplaceProductsAction->shouldReceive('execute')
        ->once()
        ->with($this->connection, $products);

    Log::shouldReceive('info')->once()->withAnyArgs();

    // Mock Prometheus facade
    $mockCounterSum = Mockery::mock(Counter::class);
    $mockCounterSum->shouldReceive('inc')->once()->with(3.0, [MarketplaceEnum::OZON->value, 'products']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_sum')
        ->andReturn($mockCounterSum);

    $mockCounterCount = Mockery::mock(Counter::class);
    $mockCounterCount->shouldReceive('inc')->once()->with(1, [MarketplaceEnum::OZON->value, 'products']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_duration_seconds_count')
        ->andReturn($mockCounterCount);

    $this->service->processSuccess($data);
});

it('processes failed sync result', function (): void {
    $data = [
        'operation' => 'inventory',
        'marketplace' => MarketplaceEnum::OZON->value,
        'error_message' => 'Something went wrong',
    ];

    Log::shouldReceive('error')->once()->withAnyArgs();

    // Mock Prometheus facade
    $mockCounterError = Mockery::mock(Counter::class);
    $mockCounterError->shouldReceive('inc')->once()->with(1, [MarketplaceEnum::OZON->value, 'service_error']);
    Prometheus::shouldReceive('addCounter')
        ->with('sync_errors_total')
        ->andReturn($mockCounterError);

    $this->service->processFailure($data);
});

it('logs warning if marketplace is empty', function (): void {
    $data = [
        'operation' => 'inventory',
        'organization_id' => $this->organization->id,
        'marketplace' => '', // Empty marketplace
        'data' => [['external_id' => 'item1', 'quantity' => 10]],
        'duration' => 1.5,
    ];

    Log::shouldReceive('warning')->once()->with('marketplace empty');

    $this->service->processSuccess($data);
});

it('logs warning if connection not found', function (): void {
    $data = [
        'operation' => 'inventory',
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'data' => [['external_id' => 'item1', 'quantity' => 10]],
        'duration' => 1.5,
    ];

    $this->tenantManager->shouldReceive('setOrganizationId')
        ->once()
        ->with($this->organization->id);
    $this->connectionRepository->shouldReceive('findByOrganizationAndMarketplace')
        ->once()
        ->with($this->organization->id, MarketplaceEnum::OZON)
        ->andReturn(null); // Connection not found

    Log::shouldReceive('info')->once()->withAnyArgs();
    Log::shouldReceive('warning')->once()->with(sprintf('Connection not found for Org: %d, Marketplace: %s', $this->organization->id, MarketplaceEnum::OZON->value)); // Mock Log::warning

    $this->service->processSuccess($data);
});
