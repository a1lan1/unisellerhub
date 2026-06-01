<?php

declare(strict_types=1);

use App\Modules\Order\Domain\Models\Order;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Exceptions\TenantNotSetException;
use App\Modules\User\Domain\Models\User;

test('it prevents cross-tenant access for products', function (): void {
    // Create two users from different organizations
    $userA = User::factory()->withBaseRoles()->create();
    $userB = User::factory()->withBaseRoles()->create();

    // Create a product for Organization A
    $productA = Product::factory()->create(['organization_id' => $userA->organization_id]);

    // Create a product for Organization B
    $productB = Product::factory()->create(['organization_id' => $userB->organization_id]);

    // Log in as User A
    $this->actingAs($userA);

    // Assert User A sees only their product
    expect(Product::count())->toBe(1)
        ->and(Product::first()->id)->toBe($productA->id)
        ->and(Product::where('id', $productB->id)->exists())->toBeFalse();
});

test('it prevents cross-tenant access for orders', function (): void {
    $userA = User::factory()->withBaseRoles()->create();
    $userB = User::factory()->withBaseRoles()->create();

    $orderA = Order::factory()->create(['organization_id' => $userA->organization_id]);
    $orderB = Order::factory()->create(['organization_id' => $userB->organization_id]);

    $this->actingAs($userA);

    expect(Order::count())->toBe(1)
        ->and(Order::first()->id)->toBe($orderA->id)
        ->and(Order::where('id', $orderB->id)->exists())->toBeFalse();
});

test('it throws exception when tenant is not set', function (): void {
    // Clear any active tenant
    resolve(TenantManager::class)->clear();

    // Attempting to query a scoped model should throw TenantNotSetException
    Product::all();
})->throws(TenantNotSetException::class);
