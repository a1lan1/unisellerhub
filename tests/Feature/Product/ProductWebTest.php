<?php

declare(strict_types=1);

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

it('can display the products page', function (): void {
    $products = ProductListing::factory()
        ->for(Product::factory()->for($this->organization))
        ->count(3)
        ->create();

    get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Products/Index')
            ->has('products.data', 3)
            ->has('products.data.0', fn (Assert $json): Assert => $json
                ->hasAll([
                    'id',
                    'name',
                    'sku',
                    'marketplace',
                    'external_id',
                    'price',
                    'formatted_price',
                    'status',
                    'last_synced_at',
                    'product',
                ])
                ->has('product', fn (Assert $json): Assert => $json
                    ->hasAll(['id', 'name', 'sku', 'cost_price', 'organization_id'])
                )
            )
            ->has('filters')
        );
});

it('redirects unauthenticated users from products page', function (): void {
    $this->postJson(route('logout'));
    get(route('products.index'))
        ->assertRedirect(route('login'));
});
