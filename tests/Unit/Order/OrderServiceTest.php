<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Data\SyncOrdersData;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Order\Infrastructure\Jobs\SyncOrdersJob;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Cknow\Money\Money;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Support\Facades\Bus;

beforeEach(function (): void {
    Bus::fake();
    $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
    // Create a real instance of OrderService and inject the mocked repository
    $this->orderService = new OrderService($this->orderRepository);
});

it('returns zero stats if user has no organization', function (): void {
    $user = User::factory()->make(['organization_id' => null]);
    $user->setRelation('organization', null);

    // No expectations needed for orderRepository as it shouldn't be called for a user without organization
    $this->orderRepository->shouldNotReceive('getOrdersCountForDashboard');
    $this->orderRepository->shouldNotReceive('getSalesAmountForDashboard');
    $this->orderRepository->shouldNotReceive('getSalesTrend');
    $this->orderRepository->shouldNotReceive('getMarketplaceDistribution');
    $this->orderRepository->shouldNotReceive('getPaginatedOrders');

    $stats = $this->orderService->getDashboardStats($user);

    expect($stats)->toEqual([
        'today_orders' => 0,
        'today_sales' => 0.0,
        'trend' => collect(),
        'distribution' => collect(),
    ]);
});

it('gets dashboard stats for a user with organization', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $organization = Organization::factory()->create();
    $user->organization_id = $organization->id;
    $user->setRelation('organization', $organization);

    $today = now()->toDateString();
    $mockOrdersCount = 10;
    $mockSalesAmount = 1500.0;
    $mockSalesTrend = collect(['2023-01-01' => 100]);
    $mockMarketplaceDistribution = collect(['OZON' => 5, 'WB' => 5]);

    $this->orderRepository->shouldReceive('getOrdersCountForDashboard')
        ->once()
        ->with($today)
        ->andReturn($mockOrdersCount);
    $this->orderRepository->shouldReceive('getSalesAmountForDashboard')
        ->once()
        ->with($today)
        ->andReturn($mockSalesAmount);
    $this->orderRepository->shouldReceive('getSalesTrend')
        ->once()
        ->with(Mockery::type(CarbonImmutable::class), Mockery::type(CarbonImmutable::class))
        ->andReturn($mockSalesTrend);
    $this->orderRepository->shouldReceive('getMarketplaceDistribution')
        ->once()
        ->withNoArgs()
        ->andReturn($mockMarketplaceDistribution);

    $stats = $this->orderService->getDashboardStats($user);

    expect($stats)->toEqual([
        'today_orders' => $mockOrdersCount,
        'today_sales' => $mockSalesAmount,
        'trend' => $mockSalesTrend,
        'distribution' => $mockMarketplaceDistribution,
    ]);
});

it('gets dashboard stats for a user with organization and specific date', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $organization = Organization::factory()->create();
    $user->organization_id = $organization->id;
    $user->setRelation('organization', $organization);

    $specificDate = '2023-01-15';
    $mockOrdersCount = 5;
    $mockSalesAmount = 750.0;
    $mockSalesTrend = collect(['2023-01-15' => 50]);
    $mockMarketplaceDistribution = collect(['OZON' => 3, 'WB' => 2]);

    $this->orderRepository->shouldReceive('getOrdersCountForDashboard')
        ->once()
        ->with($specificDate)
        ->andReturn($mockOrdersCount);
    $this->orderRepository->shouldReceive('getSalesAmountForDashboard')
        ->once()
        ->with($specificDate)
        ->andReturn($mockSalesAmount);
    $this->orderRepository->shouldReceive('getSalesTrend')
        ->once()
        ->with(Mockery::type(CarbonImmutable::class), Mockery::type(CarbonImmutable::class))
        ->andReturn($mockSalesTrend);
    $this->orderRepository->shouldReceive('getMarketplaceDistribution')
        ->once()
        ->withNoArgs()
        ->andReturn($mockMarketplaceDistribution);

    $stats = $this->orderService->getDashboardStats($user, $specificDate);

    expect($stats)->toEqual([
        'today_orders' => $mockOrdersCount,
        'today_sales' => $mockSalesAmount,
        'trend' => $mockSalesTrend,
        'distribution' => $mockMarketplaceDistribution,
    ]);
});

it('returns empty paginator if user has no organization', function (): void {
    $user = User::factory()->make(['organization_id' => null]);
    $user->setRelation('organization', null);

    $filter = new OrderFilterData(pagination: new Pagination(perPage: 15, page: 1));

    // No expectations needed for orderRepository as it shouldn't be called for a user without organization
    $this->orderRepository->shouldNotReceive('getPaginatedOrders');

    $paginator = $this->orderService->getPaginatedOrders($user, $filter);

    expect($paginator)->toBeInstanceOf(ConcreteLengthAwarePaginator::class);
    expect($paginator->total())->toBe(0);
    expect($paginator->perPage())->toBe(15);
});

it('gets paginated orders for a user with organization', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $organization = Organization::factory()->create();
    $user->organization_id = $organization->id;
    $user->setRelation('organization', $organization);

    $filter = new OrderFilterData(pagination: new Pagination(perPage: 15, page: 1));
    $mockPaginator = new ConcreteLengthAwarePaginator([], 0, 15);

    $this->orderRepository->shouldReceive('getPaginatedOrders')
        ->once()
        ->with($filter)
        ->andReturn($mockPaginator);

    $paginator = $this->orderService->getPaginatedOrders($user, $filter);

    expect($paginator)->toEqual($mockPaginator);
});

it('creates a new order if not found', function (): void {
    $marketplace = MarketplaceEnum::OZON;
    $orderData = new OrderData(
        external_id: new ExternalOrderId('ext-123'),
        status: 'new',
        total_price: Money::RUB(10000),
        items: [],
        order_date: CarbonImmutable::now(),
        delivery_info: ['address' => 'Test Address']
    );
    $organizationId = 1;
    $newOrder = Order::factory()->make();

    $this->orderRepository->shouldReceive('findByExternalId')
        ->once()
        ->with($marketplace, $orderData->external_id->getValue())
        ->andReturn(null);
    $this->orderRepository->shouldReceive('create')
        ->once()
        ->with(Mockery::subset([
            'organization_id' => $organizationId,
            'marketplace' => $marketplace,
            'external_id' => $orderData->external_id->getValue(),
            'status' => $orderData->status,
            'total_price' => $orderData->total_price->getAmount(),
            'order_date' => $orderData->order_date,
            'delivery_info' => $orderData->delivery_info,
        ]))
        ->andReturn($newOrder);

    $order = $this->orderService->updateOrCreateOrder($marketplace, $orderData, $organizationId);

    expect($order)->toEqual($newOrder);
});

it('updates an existing order if found', function (): void {
    $marketplace = MarketplaceEnum::OZON;
    $existingOrder = Order::factory()->make([
        'marketplace' => $marketplace,
        'external_id' => 'ext-123',
        'status' => 'pending',
        'total_price' => 50.0,
    ]);
    $orderData = new OrderData(
        external_id: new ExternalOrderId('ext-123'),
        status: 'completed',
        total_price: Money::RUB(15000),
        items: [],
        order_date: CarbonImmutable::now(),
        delivery_info: ['address' => 'Updated Address']
    );
    $organizationId = 1;
    $updatedOrder = clone $existingOrder;
    $updatedOrder->status = 'completed';
    $updatedOrder->total_price = Money::RUB(15000);

    $this->orderRepository->shouldReceive('findByExternalId')
        ->once()
        ->with($marketplace, $orderData->external_id->getValue())
        ->andReturn($existingOrder);
    $this->orderRepository->shouldReceive('update')
        ->once()
        ->with(Mockery::on(fn ($arg) => $arg->is($existingOrder)), Mockery::subset([
            'status' => $orderData->status,
            'total_price' => $orderData->total_price->getAmount(),
            'order_date' => $orderData->order_date,
            'delivery_info' => $orderData->delivery_info,
        ]))
        ->andReturn($updatedOrder);

    $order = $this->orderService->updateOrCreateOrder($marketplace, $orderData, $organizationId);

    expect($order)->toEqual($updatedOrder);
});

it('dispatches SyncOrdersJob', function (): void {
    $dto = new SyncOrdersData(organizationId: 1, marketplace: MarketplaceEnum::OZON);

    $this->orderRepository->shouldNotReceive('syncOrders');

    $this->orderService->syncOrders($dto);

    Bus::assertDispatched(SyncOrdersJob::class, fn (SyncOrdersJob $job): bool => $job->organizationId === $dto->organizationId && $job->marketplace === $dto->marketplace);
});
