<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\SyncProductsData;
use App\Modules\Product\Domain\Interfaces\ProductServiceInterface;
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

it('dispatches product sync job without marketplace', function (): void {
    $this->mock(ProductServiceInterface::class)
        ->shouldReceive('syncProducts')
        ->once()
        ->withArgs(fn (SyncProductsData $data): bool => $data->organizationId === $this->organization->id && ! $data->marketplace instanceof MarketplaceEnum);

    postJson(route('api.products.sync'))
        ->assertOk()
        ->assertJson(['message' => 'Sync job dispatched!']);
});

it('dispatches product sync job with specified marketplace', function (): void {
    $marketplace = MarketplaceEnum::OZON;

    $this->mock(ProductServiceInterface::class)
        ->shouldReceive('syncProducts')
        ->once()
        ->withArgs(fn (SyncProductsData $data): bool => $data->organizationId === $this->organization->id && $data->marketplace === $marketplace);

    postJson(route('api.products.sync'), ['marketplace' => $marketplace->value])
        ->assertOk()
        ->assertJson(['message' => 'Sync job dispatched!']);
});

it('validates marketplace enum for product sync', function (): void {
    postJson(route('api.products.sync'), ['marketplace' => 'invalid_marketplace'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['marketplace']);
});

it('requires authentication to dispatch product sync job', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.products.sync'))
        ->assertUnauthorized();
});
