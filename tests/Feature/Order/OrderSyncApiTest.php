<?php

declare(strict_types=1);

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\SyncOrdersData;
use App\Modules\Order\Domain\Interfaces\OrderServiceInterface;
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

it('dispatches order sync job without marketplace', function (): void {
    $this->mock(OrderServiceInterface::class)
        ->shouldReceive('syncOrders')
        ->once()
        ->withArgs(fn (SyncOrdersData $data): bool => $data->organizationId === $this->organization->id && ! $data->marketplace instanceof MarketplaceEnum);

    postJson(route('api.orders.sync'))
        ->assertOk()
        ->assertJson(['message' => 'Orders sync job dispatched!']);
});

it('dispatches order sync job with specified marketplace', function (): void {
    $marketplace = MarketplaceEnum::OZON;

    $this->mock(OrderServiceInterface::class)
        ->shouldReceive('syncOrders')
        ->once()
        ->withArgs(fn (SyncOrdersData $data): bool => $data->organizationId === $this->organization->id && $data->marketplace === $marketplace);

    postJson(route('api.orders.sync'), ['marketplace' => $marketplace->value])
        ->assertOk()
        ->assertJson(['message' => 'Orders sync job dispatched!']);
});

it('validates marketplace enum', function (): void {
    postJson(route('api.orders.sync'), ['marketplace' => 'invalid_marketplace'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['marketplace']);
});

it('requires authentication to dispatch order sync job', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.orders.sync'))
        ->assertUnauthorized();
});
