<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;

it('dispatches inventory pull job for a specific marketplace', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('api.inventory.pull'), [
        'marketplace' => MarketplaceEnum::WB->value,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Inventory pull job dispatched!']);
});

it('dispatches bulk inventory pull job for selected items', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['organization_id' => $user->organization_id]);
    $warehouse = Warehouse::factory()->create(['organization_id' => $user->organization_id]);

    $listings = ProductListing::factory()->count(3)->create([
        'product_id' => $product->id,
    ]);

    $inventory = $listings->map(fn ($listing) => Inventory::factory()->create([
        'product_listing_id' => $listing->id,
        'warehouse_id' => $warehouse->id,
    ]));

    $response = $this->postJson(route('api.inventory.pull_bulk'), [
        'ids' => $inventory->pluck('id')->toArray(),
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Bulk pull job dispatched for 3 items!']);
});
