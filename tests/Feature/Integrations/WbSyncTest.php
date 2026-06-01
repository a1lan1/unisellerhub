<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockProduct;
use App\Modules\Order\Domain\Actions\SyncOrdersFromMarketplaceAction;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->actingAs($this->user);
});

it('syncs wildberries products correctly with money casting', function (): void {
    $token = 'wb_token_'.Str::random(10);

    $connection = $this->user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Store',
        'credentials' => ['token' => $token],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::factory()->create(['marketplace' => 'wb']);
    $mockAccount->credentials()->create(['key' => 'token', 'value' => $token]);

    $mockProduct = MockProduct::factory()->create([
        'marketplace' => MarketplaceEnum::WB,
        'mock_marketplace_account_id' => $mockAccount->id,
        'price' => 199990,
    ]);

    Http::fake([
        '*/content/v2/get/cards/list' => Http::response([
            'cards' => [
                [
                    'nmId' => (int) $mockProduct->external_id,
                    'vendorCode' => $mockProduct->vendor_code,
                    'title' => 'WB Product',
                    'brand' => 'Brand',
                    'subjectName' => 'Category',
                    'photos' => [],
                    'characteristics' => [],
                    'price' => 199990,
                ],
            ],
        ]),
    ]);

    resolve(SyncProductsFromMarketplaceAction::class)->execute($connection);

    $listing = ProductListing::where('external_id', $mockProduct->external_id)->first();
    expect($listing)->not->toBeNull()
        ->and((int) $listing->price->getAmount())->toBe(199990);
});

it('syncs wildberries orders correctly with status mapping', function (): void {
    $connection = $this->user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::WB,
        'name' => 'WB Store',
        'credentials' => ['token' => 'wb-token'],
        'is_active' => true,
    ]);

    $externalOrderId = '987654321';

    Http::fake([
        '*/api/v3/orders*' => Http::response([
            'orders' => [
                [
                    'id' => (int) $externalOrderId,
                    'status' => 'new',
                    'price' => 500050,
                    'createdAt' => now()->toIso8601String(),
                    'items' => [
                        [
                            'nmId' => 123456,
                            'quantity' => 1,
                            'price' => 500050,
                            'sku' => 'WB-SKU-1',
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $product = Product::factory()->create(['organization_id' => $this->user->organization_id]);
    ProductListing::factory()->create([
        'product_id' => $product->id,
        'marketplace' => 'wb',
        'external_id' => '123456',
    ]);

    resolve(SyncOrdersFromMarketplaceAction::class)->execute($connection);

    $this->assertDatabaseHas('orders', [
        'organization_id' => $this->user->organization_id,
        'external_id' => $externalOrderId,
        'total_price' => 500050,
        'marketplace' => 'wb',
    ]);
});
