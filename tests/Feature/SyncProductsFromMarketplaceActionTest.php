<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('SyncProductsFromMarketplaceAction imports products from Wildberries correctly', function (): void {
    // Prepare Seller, Organization and Connection
    $user = User::factory()->create();
    $this->actingAs($user);
    $token = 'wb_token_'.Str::random(20);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Account',
        'credentials' => ['token' => $token],
        'is_active' => true,
    ]);

    // Prepare Mock Data
    $mockAccount = MockMarketplaceAccount::create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'Mock WB',
    ]);
    $mockAccount->credentials()->create(['key' => 'token', 'value' => $token]);

    $mockProducts = MockProduct::factory(5)->create([
        'marketplace' => MarketplaceEnum::WB,
        'mock_marketplace_account_id' => $mockAccount->id,
        'price' => 199000,
    ]);

    // Fake HTTP
    Http::fake([
        '*/content/v2/get/cards/list' => Http::response([
            'cards' => $mockProducts->map(fn ($p): array => [
                'nmId' => (int) $p->external_id,
                'vendorCode' => $p->vendor_code,
                'title' => $p->name,
                'brand' => $p->brand,
                'subjectName' => $p->category,
                'photos' => array_map(fn ($url): array => ['big' => $url], $p->images),
                'characteristics' => $p->attributes,
                'price' => (int) $p->price,
            ])->all(),
        ]),
    ]);

    // Action
    $action = resolve(SyncProductsFromMarketplaceAction::class);
    $action->execute($connection);

    // Assertions
    expect(Product::count())->toBe(5);
    expect(ProductListing::count())->toBe(5);
});

test('SyncProductsFromMarketplaceAction imports products from Ozon correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $clientId = 'ozon_client_'.Str::random(8);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'name' => 'Ozon Account',
        'credentials' => ['client_id' => $clientId, 'api_key' => 'key'],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::create([
        'marketplace' => MarketplaceEnum::OZON,
        'name' => 'Mock Ozon',
    ]);
    $mockAccount->credentials()->create(['key' => 'client_id', 'value' => $clientId]);

    $mockProducts = MockProduct::factory(3)->create([
        'marketplace' => MarketplaceEnum::OZON,
        'mock_marketplace_account_id' => $mockAccount->id,
        'price' => 150050,
    ]);

    // Fake HTTP
    Http::fake([
        '*/product/list' => Http::response([
            'result' => [
                'items' => $mockProducts->map(fn ($p): array => [
                    'product_id' => (int) $p->external_id,
                    'offer_id' => $p->vendor_code,
                ])->all(),
            ],
            'total' => 3,
        ]),
        '*/product/info/list' => Http::response([
            'result' => [
                'items' => $mockProducts->map(fn ($p): array => [
                    'id' => (int) $p->external_id,
                    'offer_id' => $p->vendor_code,
                    'name' => $p->name,
                    'price' => number_format($p->price / 100, 2, '.', ''),
                    'images' => $p->images,
                ])->all(),
            ],
        ]),
    ]);

    $action = resolve(SyncProductsFromMarketplaceAction::class);
    $action->execute($connection);

    expect(Product::count())->toBe(3);
});

test('SyncProductsFromMarketplaceAction updates existing products by vendor_code', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $token = 'wb_token_'.Str::random(20);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Account',
        'credentials' => ['token' => $token],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'Mock WB',
    ]);
    $mockAccount->credentials()->create(['key' => 'token', 'value' => $token]);

    $vendorCode = 'TEST-SKU-123';
    $mockProduct = MockProduct::factory()->create([
        'marketplace' => MarketplaceEnum::WB,
        'mock_marketplace_account_id' => $mockAccount->id,
        'vendor_code' => $vendorCode,
        'name' => 'Original Name',
    ]);

    Http::fake([
        '*/content/v2/get/cards/list' => Http::sequence()
            ->push(['cards' => [['nmId' => (int) $mockProduct->external_id, 'vendorCode' => $vendorCode, 'title' => 'Original Name', 'price' => 100000]]])
            ->push(['cards' => [['nmId' => (int) $mockProduct->external_id, 'vendorCode' => $vendorCode, 'title' => 'Updated Name', 'price' => 120000]]]),
    ]);

    $action = resolve(SyncProductsFromMarketplaceAction::class);
    $action->execute($connection);

    expect(Product::where('sku', $vendorCode)->first()->name)->toBe('Original Name');

    $action->execute($connection);

    expect(Product::count())->toBe(1);
    expect(Product::where('sku', $vendorCode)->first()->name)->toBe('Updated Name');
});
