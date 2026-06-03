<?php

declare(strict_types=1);

use App\Modules\Inventory\Domain\Data\SyncMoySkladStockData;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
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

it('dispatches MoySklad stock sync job', function (): void {
    $this->mock(InventoryServiceInterface::class)
        ->shouldReceive('syncMoySkladStock')
        ->once()
        ->withArgs(fn (SyncMoySkladStockData $data): bool => $data->organizationId === $this->organization->id);

    postJson(route('api.inventory.sync_ms'))
        ->assertOk()
        ->assertJson(['message' => 'MoySklad stock sync job dispatched!']);
});

it('requires authentication to dispatch MoySklad stock sync job', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.inventory.sync_ms'))
        ->assertUnauthorized();
});
