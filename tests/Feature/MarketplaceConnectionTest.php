<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\User\Domain\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list marketplace connections', function (): void {
    MarketplaceConnection::factory()->count(3)->create(['organization_id' => $this->user->organization_id]);

    // Another organization's connection (should not be visible)
    MarketplaceConnection::factory()->create();

    $response = $this->getJson(route('api.marketplace-connections.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3);
});

it('can create a marketplace connection with encrypted credentials', function (): void {
    $payload = [
        'marketplace' => MarketplaceEnum::WB->value,
        'name' => 'My WB Shop',
        'credentials' => [
            'token' => 'wb-secret-token-123',
        ],
        'is_active' => true,
    ];

    $response = $this->postJson(route('api.marketplace-connections.store'), $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('marketplace_connections', [
        'organization_id' => $this->user->organization_id,
        'marketplace' => MarketplaceEnum::WB->value,
        'name' => 'My WB Shop',
    ]);

    $connection = MarketplaceConnection::where('organization_id', $this->user->organization_id)->first();
    expect($connection->credentials['token'])->toBe('wb-secret-token-123');
});

it('can update a marketplace connection', function (): void {
    $connection = MarketplaceConnection::factory()->create(['organization_id' => $this->user->organization_id]);

    $response = $this->putJson(route('api.marketplace-connections.update', $connection), [
        'name' => 'Updated Name',
        'is_active' => false,
    ]);

    $response->assertStatus(200);
    expect($connection->fresh()->name)->toBe('Updated Name')
        ->and($connection->fresh()->is_active)->toBeFalse();
});

it('can delete a marketplace connection', function (): void {
    $connection = MarketplaceConnection::factory()->create(['organization_id' => $this->user->organization_id]);

    $response = $this->deleteJson(route('api.marketplace-connections.destroy', $connection));

    $response->assertStatus(204);
    $this->assertDatabaseMissing('marketplace_connections', ['id' => $connection->id]);
});
