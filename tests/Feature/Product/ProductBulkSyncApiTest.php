<?php

declare(strict_types=1);

use App\Modules\Product\Domain\Data\SyncBulkProductData;
use App\Modules\Product\Domain\Interfaces\ProductServiceInterface;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('dispatches bulk product sync job with provided ids', function (): void {
    $products = Product::factory()->for($this->organization)->count(3)->create();
    $productIds = $products->pluck('id')->toArray();

    foreach ($products as $product) {
        ProductListing::factory()->for($product)->create();
    }

    $this->mock(ProductServiceInterface::class)
        ->shouldReceive('syncBulkProducts')
        ->once()
        ->withArgs(fn (SyncBulkProductData $data): bool => $data->organizationId === $this->organization->id && $data->ids === $productIds);

    postJson(route('api.products.sync_bulk'), ['ids' => $productIds])
        ->assertOk()
        ->assertJson(['message' => 'Bulk sync job dispatched for '.count($productIds).' items!']);
});

it('dispatches bulk product sync job with empty ids array', function (): void {
    $productIds = [];

    postJson(route('api.products.sync_bulk'), ['ids' => $productIds])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['ids']);
});

it('validates ids as array for bulk product sync', function (): void {
    postJson(route('api.products.sync_bulk'), ['ids' => 'not_an_array'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['ids']);

    $product = Product::factory()->for($this->organization)->create();
    $validProductId = $product->id;
    postJson(route('api.products.sync_bulk'), ['ids' => [$validProductId, 99999]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['ids.1']);
});

it('requires authentication to dispatch bulk product sync job', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.products.sync_bulk'), ['ids' => [1, 2]])
        ->assertUnauthorized();
});
