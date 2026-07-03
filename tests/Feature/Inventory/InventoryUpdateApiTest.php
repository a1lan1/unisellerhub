<?php

declare(strict_types=1);

use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\patchJson;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can update inventory and push to marketplace', function (): void {
    $productListing = ProductListing::factory()
        ->for(Product::factory()->for($this->organization))
        ->create();

    $warehouse = Warehouse::factory()->create();

    $inventory = Inventory::factory()
        ->for($productListing, 'listing')
        ->for($warehouse)
        ->create();

    $newQuantity = $inventory->quantity->getValue() + 5;

    // Mock the original inventory attributes
    $mockedInventory = $this->mock(Inventory::class)->makePartial();
    $mockedInventory->shouldReceive('getAttribute')->with('id')->andReturn($inventory->id);
    $mockedInventory->shouldReceive('getAttribute')->with('product_listing_id')->andReturn($inventory->product_listing_id);
    $mockedInventory->shouldReceive('getAttribute')->with('updated_at')->andReturn($inventory->updated_at);
    $mockedInventory->shouldReceive('getAttribute')->with('reserved')->andReturn($inventory->reserved);

    // Create a mocked inventory with the updated quantity
    $updatedMockedInventory = $this->mock(Inventory::class)->makePartial();
    $updatedMockedInventory->shouldReceive('getAttribute')->with('id')->andReturn($inventory->id);
    $updatedMockedInventory->shouldReceive('getAttribute')->with('quantity')->andReturn(new Quantity($newQuantity)); // Return updated quantity
    $updatedMockedInventory->shouldReceive('getAttribute')->with('product_listing_id')->andReturn($inventory->product_listing_id);
    $updatedMockedInventory->shouldReceive('getAttribute')->with('updated_at')->andReturn($inventory->updated_at);
    $updatedMockedInventory->shouldReceive('getAttribute')->with('reserved')->andReturn($inventory->reserved);

    $mockedProduct = $this->mock(Product::class);
    $mockedProduct->shouldReceive('getAttribute')->with('name')->andReturn($inventory->listing->product->name);
    $mockedProduct->shouldReceive('getAttribute')->with('sku')->andReturn($inventory->listing->product->sku);

    $mockedListing = $this->mock(ProductListing::class);
    $mockedListing->shouldReceive('getAttribute')->with('product')->andReturn($mockedProduct);
    $mockedListing->shouldReceive('getAttribute')->with('marketplace')->andReturn($inventory->listing->marketplace);

    $updatedMockedInventory->shouldReceive('getAttribute')->with('listing')->andReturn($mockedListing);
    $mockedInventory->shouldReceive('getAttribute')->with('listing')->andReturn($mockedListing);

    $mockedWarehouse = $this->mock(Warehouse::class);
    $mockedWarehouse->shouldReceive('getAttribute')->with('name')->andReturn($warehouse->name);
    $updatedMockedInventory->shouldReceive('getAttribute')->with('warehouse')->andReturn($mockedWarehouse);
    $mockedInventory->shouldReceive('getAttribute')->with('warehouse')->andReturn($mockedWarehouse);

    $this->mock(InventoryServiceInterface::class)
        ->shouldReceive('updateInventoryAndPushToMarketplace')
        ->once()
        ->with($inventory->id, $newQuantity)
        ->andReturn($updatedMockedInventory);

    patchJson(route('api.inventory.update'), [
        'id' => $inventory->id,
        'quantity' => $newQuantity,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll([
            'id',
            'product_name',
            'sku',
            'marketplace',
            'warehouse_name',
            'quantity',
            'reserved',
            'available',
            'listing_id',
            'updated_at',
        ])
            ->where('id', $inventory->id)
            ->where('product_name', $inventory->listing->product->name)
            ->where('sku', $inventory->listing->product->sku->getValue())
            ->where('marketplace', $inventory->listing->marketplace->value)
            ->where('warehouse_name', $warehouse->name)
            ->where('quantity', $newQuantity)
            ->where('reserved', $inventory->reserved->getValue())
            ->where('listing_id', $inventory->product_listing_id)
        );
});

it('validates update inventory data', function (): void {
    patchJson(route('api.inventory.update'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['id', 'quantity']);

    patchJson(route('api.inventory.update'), [
        'id' => 9999,
        'quantity' => -1,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['id', 'quantity']);

    $inventory = Inventory::factory()->create([]);
    patchJson(route('api.inventory.update'), [
        'id' => $inventory->id,
        'quantity' => 'not_an_integer',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

it('requires authentication to update inventory', function (): void {
    $this->postJson(route('logout'));
    $inventory = Inventory::factory()->create([]);

    patchJson(route('api.inventory.update'), [
        'id' => $inventory->id,
        'quantity' => 10,
    ])
        ->assertUnauthorized();
});
