<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use App\Modules\Order\Infrastructure\Repositories\EloquentOrderRepository;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Cknow\Money\Money;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = new EloquentOrderRepository;
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    // Set the organization_id in TenantManager for the scope
    resolve(TenantManager::class)->setOrganizationId($this->organization->id);
});

it('gets orders count for dashboard by date', function (): void {
    Order::factory()->count(5)->create(['organization_id' => $this->organization->id, 'order_date' => now()->toDateString()]);
    Order::factory()->count(3)->create(['organization_id' => $this->organization->id, 'order_date' => now()->subDay()->toDateString()]);

    $count = $this->repository->getOrdersCountForDashboard(now()->toDateString());
    expect($count)->toBe(5);
});

it('gets sales amount for dashboard by date', function (): void {
    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => now()->toDateString(), 'total_price' => Money::RUB(10050)->getAmount()]); // Use Money and getAmount
    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => now()->toDateString(), 'total_price' => Money::RUB(20000)->getAmount()]); // Use Money and getAmount
    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => now()->subDay()->toDateString(), 'total_price' => Money::RUB(5000)->getAmount()]); // Use Money and getAmount

    $amount = $this->repository->getSalesAmountForDashboard(now()->toDateString());
    expect($amount)->toBe(30050.0); // Expect float for display, but the sum is in minor units
});

it('gets sales trend', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2023-01-31'));

    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => '2023-01-29', 'total_price' => Money::RUB(10000)->getAmount()]);
    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => '2023-01-29', 'total_price' => Money::RUB(5000)->getAmount()]);
    Order::factory()->create(['organization_id' => $this->organization->id, 'order_date' => '2023-01-30', 'total_price' => Money::RUB(20000)->getAmount()]);

    $startDate = CarbonImmutable::parse('2023-01-01');
    $endDate = CarbonImmutable::parse('2023-01-31');

    $trend = $this->repository->getSalesTrend($startDate, $endDate);

    expect($trend)->toBeInstanceOf(Collection::class);
    expect($trend->where('date', '2023-01-29')->first()['aggregate'])->toBe(15000);
    expect($trend->where('date', '2023-01-30')->first()['aggregate'])->toBe(20000);
    expect($trend->where('date', '2023-01-31')->first()['aggregate'])->toBe(0); // No orders on 31st
});

it('gets marketplace distribution', function (): void {
    Order::factory()->count(3)->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::OZON]);
    Order::factory()->count(2)->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::WB]);
    Order::factory()->count(1)->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::YANDEX]);

    $distribution = $this->repository->getMarketplaceDistribution();

    expect($distribution)->toBeInstanceOf(Collection::class);
    expect($distribution->where('marketplace', MarketplaceEnum::OZON->value)->first()['count'])->toBe(3);
    expect($distribution->where('marketplace', MarketplaceEnum::WB->value)->first()['count'])->toBe(2);
    expect($distribution->where('marketplace', MarketplaceEnum::YANDEX->value)->first()['count'])->toBe(1);
});

it('finds an order by external ID and marketplace', function (): void {
    $order = Order::factory()->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::OZON, 'external_id' => 'ozon-123']);
    $foundOrder = $this->repository->findByExternalId(MarketplaceEnum::OZON, 'ozon-123');
    expect($foundOrder->id)->toBe($order->id);
});

it('returns null if order not found by external ID and marketplace', function (): void {
    $foundOrder = $this->repository->findByExternalId(MarketplaceEnum::OZON, 'non-existent');
    expect($foundOrder)->toBeNull();
});

it('creates an order', function (): void {
    $data = [
        'organization_id' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON,
        'external_id' => new ExternalOrderId('new-order-1'),
        'status' => OrderStatusEnum::NEW,
        'total_price' => Money::RUB(15000)->getAmount(),
        'order_date' => now(),
        'delivery_info' => ['address' => 'Some address'],
    ];
    $order = $this->repository->create($data);
    expect($order->external_id->getValue())->toBe('new-order-1');
    $this->assertDatabaseHas('orders', ['external_id' => 'new-order-1']);
});

it('updates an order', function (): void {
    $order = Order::factory()->create(['organization_id' => $this->organization->id, 'status' => OrderStatusEnum::NEW]);
    $data = ['status' => OrderStatusEnum::COMPLETED];
    $updatedOrder = $this->repository->update($order, $data);
    expect($updatedOrder->status)->toBe(OrderStatusEnum::COMPLETED);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatusEnum::COMPLETED->value]);
});

it('gets paginated orders with filters', function (): void {
    $order1 = Order::factory()->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::OZON, 'status' => OrderStatusEnum::NEW, 'order_date' => now()->subDays(2)]);
    $order2 = Order::factory()->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::WB, 'status' => OrderStatusEnum::COMPLETED, 'order_date' => now()->subDay()]);
    Order::factory()->count(5)->create(['organization_id' => $this->organization->id, 'marketplace' => MarketplaceEnum::OZON, 'order_date' => now()->subDays(3)]);

    $filter = OrderFilterData::from([
        'organizationId' => $this->organization->id,
        'marketplace' => MarketplaceEnum::OZON->value,
        'status' => OrderStatusEnum::NEW->value,
        'pagination' => new Pagination(perPage: 10, page: 1),
    ]);

    $paginator = $this->repository->getPaginatedOrders($filter);

    expect($paginator->total())->toBe(6); // 1 new OZON + 5 other OZON
    expect($paginator->pluck('id'))->toContain($order1->id); // Check if it contains the ID, not necessarily first
});

it('sums total amount by status', function (): void {
    Order::factory()->create(['organization_id' => $this->organization->id, 'status' => OrderStatusEnum::NEW, 'total_price' => Money::RUB(10000)->getAmount()]);
    Order::factory()->create(['organization_id' => $this->organization->id, 'status' => OrderStatusEnum::NEW, 'total_price' => Money::RUB(20000)->getAmount()]);
    Order::factory()->create(['organization_id' => $this->organization->id, 'status' => OrderStatusEnum::COMPLETED, 'total_price' => Money::RUB(50000)->getAmount()]);

    $sum = $this->repository->sumTotalAmountByStatus(OrderStatusEnum::NEW);
    expect($sum)->toBe(30000); // Expect integer amount in minor units
});

it('gets sales stats by currency', function (): void {
    Order::factory()->create(['organization_id' => $this->organization->id, 'total_price' => Money::RUB(10000)->getAmount()]);
    Order::factory()->create(['organization_id' => $this->organization->id, 'total_price' => Money::RUB(20000)->getAmount()]);

    $stats = $this->repository->getSalesStatsByCurrency();

    expect($stats->count)->toBe(2);
    expect($stats->totalSales->getAmount())->toBe('30000');
});

it('returns null for sales stats if no orders', function (): void {
    $stats = $this->repository->getSalesStatsByCurrency();
    expect($stats)->toBeNull();
});
