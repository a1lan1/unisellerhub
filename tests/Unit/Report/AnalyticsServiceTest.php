<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\Report\Application\Services\AnalyticsService;
use App\Modules\Report\Domain\Data\AbcAnalysisData;
use App\Modules\Report\Domain\Data\AbcAnalysisItemData;
use App\Modules\Report\Domain\Data\ProfitabilityItemData;
use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use App\Modules\Report\Domain\Repositories\AnalyticsRepositoryInterface;
use App\Modules\Report\Domain\ValueObjects\AbcSummary;
use App\Modules\Shared\Domain\ValueObjects\Percentage;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

beforeEach(function (): void {
    $this->analyticsRepository = $this->mock(AnalyticsRepositoryInterface::class);
    $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
    $this->analyticsService = new AnalyticsService($this->analyticsRepository, $this->orderRepository);

    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->setRelation('organization', $this->organization);
});

it('returns empty ABC analysis if user has no organization', function (): void {
    $userWithoutOrg = User::factory()->make(['organization_id' => null]);
    $userWithoutOrg->setRelation('organization', null);

    $this->analyticsRepository->shouldNotReceive('getProductRevenue');
    $this->analyticsRepository->shouldNotReceive('getProductListingsWithCosts');

    $result = $this->analyticsService->getAbcAnalysis($userWithoutOrg, '2026-01-31', 30);

    expect($result)->toEqual(AbcAnalysisData::emptyAnalysis());
});

it('returns empty ABC analysis if no product revenue', function (): void {
    $this->analyticsRepository->shouldReceive('getProductRevenue')
        ->once()
        ->with($this->user->organization_id, '2026-01-31', 30)
        ->andReturn(new Collection);

    $result = $this->analyticsService->getAbcAnalysis($this->user, '2026-01-31', 30);

    expect($result)->toEqual(AbcAnalysisData::emptyAnalysis());
});

it('performs ABC analysis correctly', function (): void {
    $productRevenue = new Collection([
        (object) ['sku' => 'SKU001', 'product_name' => 'Product A', 'revenue' => 80000],
        (object) ['sku' => 'SKU002', 'product_name' => 'Product B', 'revenue' => 15000],
        (object) ['sku' => 'SKU003', 'product_name' => 'Product C', 'revenue' => 5000],
    ]); // Total revenue 100000

    $this->analyticsRepository->shouldReceive('getProductRevenue')
        ->once()
        ->with($this->user->organization_id, '2026-01-31', 30)
        ->andReturn($productRevenue);

    $result = $this->analyticsService->getAbcAnalysis($this->user, '2026-01-31', 30);

    expect($result)->toBeInstanceOf(AbcAnalysisData::class);
    expect($result->summary)->toBeInstanceOf(AbcSummary::class);
    expect($result->summary->getCountForGroup(AbcGroupEnum::A))->toBe(1);
    expect($result->summary->getCountForGroup(AbcGroupEnum::B))->toBe(1);
    expect($result->summary->getCountForGroup(AbcGroupEnum::C))->toBe(1);

    expect($result->items)->toBeInstanceOf(DataCollection::class);
    expect($result->items)->toHaveCount(3);

    // Assert individual properties for each item
    expect($result->items[0])->toBeInstanceOf(AbcAnalysisItemData::class);
    expect($result->items[0]->sku)->toEqual(new Sku('SKU001'));
    expect($result->items[0]->name)->toBe('Product A');
    expect($result->items[0]->revenue)->toEqual(Money::RUB(80000));
    expect($result->items[0]->share)->toEqual(new Percentage(80.0));
    expect($result->items[0]->group)->toBe(AbcGroupEnum::A);

    expect($result->items[1])->toBeInstanceOf(AbcAnalysisItemData::class);
    expect($result->items[1]->sku)->toEqual(new Sku('SKU002'));
    expect($result->items[1]->name)->toBe('Product B');
    expect($result->items[1]->revenue)->toEqual(Money::RUB(15000));
    expect($result->items[1]->share)->toEqual(new Percentage(15.0));
    expect($result->items[1]->group)->toBe(AbcGroupEnum::B);

    expect($result->items[2])->toBeInstanceOf(AbcAnalysisItemData::class);
    expect($result->items[2]->sku)->toEqual(new Sku('SKU003'));
    expect($result->items[2]->name)->toBe('Product C');
    expect($result->items[2]->revenue)->toEqual(Money::RUB(5000));
    expect($result->items[2]->share)->toEqual(new Percentage(5.0));
    expect($result->items[2]->group)->toBe(AbcGroupEnum::C);
});

it('returns empty profitability analysis if user has no organization', function (): void {
    $userWithoutOrg = User::factory()->make(['organization_id' => null]);
    $userWithoutOrg->setRelation('organization', null);

    $this->analyticsRepository->shouldNotReceive('getProductRevenue');
    $this->analyticsRepository->shouldNotReceive('getProductListingsWithCosts');

    $result = $this->analyticsService->getProfitabilityAnalysis($userWithoutOrg);

    expect($result)->toEqual([]);
});

it('returns profitability analysis correctly', function (): void {
    $listings = new Collection([
        (object) [
            'id' => 1,
            'marketplace' => MarketplaceEnum::OZON->value,
            'sku' => 'SKU001',
            'name' => 'Product A',
            'price' => 10000, // 100.00
            'cost_price' => 5000, // 50.00
            'commission_percent' => 10.0,
            'logistic_cost' => 500, // 5.00
        ],
        (object) [
            'id' => 2,
            'marketplace' => MarketplaceEnum::WB->value,
            'sku' => 'SKU002',
            'name' => 'Product B',
            'price' => 20000, // 200.00
            'cost_price' => 8000, // 80.00
            'commission_percent' => 15.0,
            'logistic_cost' => 1000, // 10.00
        ],
    ]);

    $this->analyticsRepository->shouldReceive('getProductListingsWithCosts')
        ->once()
        ->with($this->user->organization_id)
        ->andReturn($listings);

    $result = $this->analyticsService->getProfitabilityAnalysis($this->user);

    expect($result)->toHaveCount(2);
    expect($result[0])->toBeInstanceOf(ProfitabilityItemData::class);
    expect($result[0]->id)->toBe(1);
    expect($result[0]->marketplace)->toBe(MarketplaceEnum::OZON);
    expect($result[0]->sku)->toEqual(new Sku('SKU001'));
    expect($result[0]->name)->toBe('Product A');
    expect($result[0]->price)->toEqual(Money::RUB(10000));
    expect($result[0]->costPrice)->toEqual(Money::RUB(5000));
    expect($result[0]->commissionPercent)->toEqual(new Percentage(10.0));
    expect($result[0]->logisticCost)->toEqual(Money::RUB(500));
    // Profit = 100 - (100 * 0.10) - 5 - 50 = 100 - 10 - 5 - 50 = 35
    expect($result[0]->profit)->toEqual(Money::RUB(3500));
    // Margin = (35 / 100) * 100 = 35
    expect($result[0]->margin)->toEqual(new Percentage(35.0));

    expect($result[1])->toBeInstanceOf(ProfitabilityItemData::class);
    expect($result[1]->id)->toBe(2);
    expect($result[1]->marketplace)->toBe(MarketplaceEnum::WB);
    expect($result[1]->sku)->toEqual(new Sku('SKU002'));
    expect($result[1]->name)->toBe('Product B');
    expect($result[1]->price)->toEqual(Money::RUB(20000));
    expect($result[1]->costPrice)->toEqual(Money::RUB(8000));
    expect($result[1]->commissionPercent)->toEqual(new Percentage(15.0));
    expect($result[1]->logisticCost)->toEqual(Money::RUB(1000));
    // Profit = 200 - (200 * 0.15) - 10 - 80 = 200 - 30 - 10 - 80 = 80
    expect($result[1]->profit)->toEqual(Money::RUB(8000));
    // Margin = (80 / 200) * 100 = 40
    expect($result[1]->margin)->toEqual(new Percentage(40.0));
});
