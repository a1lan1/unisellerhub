<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Inventory\Domain\Actions\SyncStockFromMarketplaceAction;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\MockMarketplace\Domain\Models\MockWarehouse;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('SyncStockFromMarketplaceAction syncs stocks for Wildberries correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $token = 'wb_token_'.Str::random(20);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Account',
        'credentials' => ['token' => $token],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::factory()->create(['marketplace' => 'wb']);
    $mockAccount->credentials()->create(['key' => 'token', 'value' => $token]);

    $mockProduct = MockProduct::factory()->create(['marketplace' => 'wb', 'mock_marketplace_account_id' => $mockAccount->id]);
    $mockWarehouse = MockWarehouse::factory()->create(['marketplace' => 'wb', 'mock_marketplace_account_id' => $mockAccount->id, 'external_id' => '12345']);

    Http::fake([
        '*/api/v3/stocks*' => Http::response([
            'stocks' => [
                [
                    'sku' => $mockProduct->vendor_code,
                    'warehouseId' => 12345,
                    'amount' => 42,
                ],
            ],
        ]),
    ]);

    $product = Product::factory()->create(['organization_id' => $user->organization_id]);

    // For WB stocks, WbClientAdapter uses 'sku' as external_product_id
    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'marketplace' => 'wb',
        'external_id' => $mockProduct->vendor_code,
    ]);

    $warehouse = Warehouse::factory()->create([
        'organization_id' => $user->organization_id,
        'external_id' => '12345',
        'marketplace' => 'wb',
    ]);

    $action = resolve(SyncStockFromMarketplaceAction::class);
    $action->execute($connection);

    $this->assertDatabaseHas('inventory', [
        'product_listing_id' => $listing->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 42,
    ]);
});

test('SyncStockFromMarketplaceAction syncs stocks for Ozon correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $clientId = 'ozon_client_'.Str::random(8);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'name' => 'Ozon Account',
        'credentials' => ['client_id' => $clientId, 'api_key' => 'key'],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::factory()->create(['marketplace' => 'ozon']);
    $mockAccount->credentials()->create(['key' => 'client_id', 'value' => $clientId]);

    $mockProduct = MockProduct::factory()->create(['marketplace' => 'ozon', 'mock_marketplace_account_id' => $mockAccount->id]);

    Http::fake([
        '*/product/info/stocks' => Http::response([
            'result' => [
                'items' => [
                    [
                        'product_id' => (int) $mockProduct->external_id,
                        'offer_id' => $mockProduct->vendor_code,
                        'stocks' => [
                            [
                                'warehouse_id' => 54321,
                                'present' => 15,
                                'reserved' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $product = Product::factory()->create(['organization_id' => $user->organization_id]);
    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'marketplace' => 'ozon',
        'external_id' => $mockProduct->external_id,
    ]);
    $warehouse = Warehouse::factory()->create([
        'organization_id' => $user->organization_id,
        'external_id' => '54321',
        'marketplace' => 'ozon',
    ]);

    $action = resolve(SyncStockFromMarketplaceAction::class);
    $action->execute($connection);

    $this->assertDatabaseHas('inventory', [
        'product_listing_id' => $listing->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 15,
    ]);
});

test('SyncStockFromMarketplaceAction updates existing stock correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Account',
        'credentials' => ['token' => 'test-token'],
        'is_active' => true,
    ]);

    $product = Product::factory()->create(['organization_id' => $user->organization_id]);
    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'marketplace' => 'wb',
        'external_id' => 'EXT-123',
    ]);
    $warehouse = Warehouse::factory()->create([
        'organization_id' => $user->organization_id,
        'external_id' => 'WH-123',
        'marketplace' => 'wb',
    ]);

    Inventory::factory()->create([
        'product_listing_id' => $listing->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 10,
    ]);

    Http::fake([
        '*/api/v3/stocks*' => Http::response([
            'stocks' => [
                [
                    'sku' => 'EXT-123',
                    'warehouseId' => 'WH-123',
                    'amount' => 99,
                ],
            ],
        ]),
    ]);

    $action = resolve(SyncStockFromMarketplaceAction::class);
    $action->execute($connection);

    expect(Inventory::where('product_listing_id', $listing->id)->count())->toBe(1);
    expect(Inventory::where('product_listing_id', $listing->id)->first()->quantity)->toBe(99);
});
