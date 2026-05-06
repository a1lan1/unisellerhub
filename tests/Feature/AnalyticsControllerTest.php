<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders abc analysis page', function (): void {
    $response = $this->get(route('analytics.abc'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Analytics/Abc'));
});

it('renders profitability analysis page', function (): void {
    $response = $this->get(route('analytics.profitability'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Analytics/Profitability'));
});

it('can update product finance data via api', function (): void {
    $product = Product::factory()->create([
        'organization_id' => $this->user->organization_id,
    ]);

    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'logistic_cost' => 0,
    ]);

    $response = $this->patchJson(route('api.analytics.update_finance'), [
        'listing_id' => $listing->id,
        'cost_price' => 450.00,
    ]);

    $response->assertStatus(200);

    expect($product->fresh()->cost_price->getAmount())->toBe('45000');
});
