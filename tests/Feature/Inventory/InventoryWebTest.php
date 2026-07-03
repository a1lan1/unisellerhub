<?php

declare(strict_types=1);

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can display the inventory page', function (): void {
    $product = Product::factory()->for($this->organization)->create();
    $productListings = ProductListing::factory()
        ->for($product)
        ->count(3)
        ->create();

    $warehouse = Warehouse::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    // Создаем по одной записи Inventory для каждого ProductListing
    foreach ($productListings as $listing) {
        Inventory::factory()->create([
            'product_listing_id' => $listing->id,
            'warehouse_id' => $warehouse->id,
        ]);
    }

    get(route('inventory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Inventory/Index')
            ->has('inventory.data', 3)
            ->has('inventory.data.0', fn (Assert $json): Assert => $json
                ->hasAll([
                    'id',
                    'product_name',
                    'sku',
                    'marketplace',
                    'warehouse_name',
                    'quantity',
                    'listing_id',
                    'reserved',
                    'available',
                    'updated_at',
                ])
                ->where('product_name', $product->name)
                ->where('sku', $product->sku->getValue())
            )
            ->has('filters')
        );
});

it('redirects unauthenticated users from inventory page', function (): void {
    $this->postJson(route('logout'));
    get(route('inventory.index'))
        ->assertRedirect(route('login'));
});
