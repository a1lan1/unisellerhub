<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Models\OrderItem;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Report\Domain\Data\AbcAnalysisItemData;
use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use App\Modules\Report\Domain\Interfaces\AnalyticsServiceInterface;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Collection;

it('calculates abc analysis correctly', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);
    resolve(TenantManager::class)->setOrganizationId($user->organization_id);

    // Create 3 products with different revenues
    // Group A (80%): Product 1
    // Group B (15%): Product 2
    // Group C (5%): Product 3

    // Set SKU on Product model, and vendor_code on ProductListing
    $product1 = Product::factory()->create(['organization_id' => $user->organization_id, 'name' => 'Prod A', 'sku' => 'PROD-SKU-A']);
    $listing1 = ProductListing::factory()->create(['product_id' => $product1->id, 'vendor_code' => 'LISTING-SKU-A']);

    $product2 = Product::factory()->create(['organization_id' => $user->organization_id, 'name' => 'Prod B', 'sku' => 'PROD-SKU-B']);
    $listing2 = ProductListing::factory()->create(['product_id' => $product2->id, 'vendor_code' => 'LISTING-SKU-B']);

    $product3 = Product::factory()->create(['organization_id' => $user->organization_id, 'name' => 'Prod C', 'sku' => 'PROD-SKU-C']);
    $listing3 = ProductListing::factory()->create(['product_id' => $product3->id, 'vendor_code' => 'LISTING-SKU-C']);

    $order = Order::factory()->create([
        'organization_id' => $user->organization_id,
        'order_date' => now(),
    ]);

    // Product 1: 8000 revenue
    OrderItem::create([
        'order_id' => $order->id,
        'product_listing_id' => $listing1->id,
        'external_product_id' => 'EXT-1',
        'quantity' => 1,
        'price' => 800000,
    ]);

    // Product 2: 1500 revenue
    OrderItem::create([
        'order_id' => $order->id,
        'product_listing_id' => $listing2->id,
        'external_product_id' => 'EXT-2',
        'quantity' => 1,
        'price' => 150000,
    ]);

    // Product 3: 500 revenue
    OrderItem::create([
        'order_id' => $order->id,
        'product_listing_id' => $listing3->id,
        'external_product_id' => 'EXT-3',
        'quantity' => 1,
        'price' => 50000,
    ]);

    $service = resolve(AnalyticsServiceInterface::class);
    $results = $service->getAbcAnalysis($user, now()->toDateString(), 30);

    expect($results->summary->getCountForGroup(AbcGroupEnum::A))->toBe(1)
        ->and($results->summary->getCountForGroup(AbcGroupEnum::B))->toBe(1)
        ->and($results->summary->getCountForGroup(AbcGroupEnum::C))->toBe(1);

    /** @var Collection<int, AbcAnalysisItemData> $items */
    $items = collect($results->items->all());
    expect($items->first(fn ($item): bool => $item->sku->getValue() === 'LISTING-SKU-A')->group)->toBe(AbcGroupEnum::A)
        ->and($items->first(fn ($item): bool => $item->sku->getValue() === 'LISTING-SKU-B')->group)->toBe(AbcGroupEnum::B)
        ->and($items->first(fn ($item): bool => $item->sku->getValue() === 'LISTING-SKU-C')->group)->toBe(AbcGroupEnum::C);
});

it('calculates profitability analysis correctly', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);
    resolve(TenantManager::class)->setOrganizationId($user->organization_id);

    $product = Product::factory()->create([
        'organization_id' => $user->organization_id,
        'cost_price' => 50000, // 500.00 RUB cost
    ]);

    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'price' => 100000, // 1000.00 RUB price
        'commission_percent' => 10.0, // 100.00 RUB commission
        'logistic_cost' => 5000, // 50.00 RUB logistics
    ]);

    $service = resolve(AnalyticsServiceInterface::class);
    $results = $service->getProfitabilityAnalysis($user);

    // Profit = 100000 - 10000 - 5000 - 50000 = 35000 (in kopeks)
    // Margin = (35000 / 100000) * 100 = 35%

    $item = $results[0];
    // Cast getAmount() to int for strict comparison
    expect((int) $item->profit->getAmount())->toBe(35000)
        ->and($item->margin->getValue())->toBe(35.0);
});
