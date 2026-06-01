<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\MockMarketplace\Domain\Models\MockOrder;
use App\Modules\Order\Domain\Actions\SyncOrdersFromMarketplaceAction;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('SyncOrdersFromMarketplaceAction syncs orders from Wildberries correctly', function (): void {
    $user = User::factory()->withBaseRoles()->create();
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

    $mockOrder = MockOrder::factory()->marketplace(MarketplaceEnum::WB)->create([
        'mock_marketplace_account_id' => $mockAccount->id,
        'status' => OrderStatusEnum::PENDING,
        'total_price' => 150050,
    ]);

    Http::fake([
        '*/api/v3/orders*' => Http::response([
            'orders' => [
                [
                    'id' => (int) $mockOrder->external_order_id,
                    'status' => $mockOrder->status->value,
                    'price' => (int) $mockOrder->total_price,
                    'createdAt' => $mockOrder->order_date->toIso8601String(),
                    'items' => [
                        [
                            'nmId' => 12345,
                            'quantity' => 2,
                            'price' => 75025,
                            'sku' => 'SKU-1',
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $product = Product::factory()->create(['organization_id' => $user->organization_id]);
    $listing = ProductListing::factory()->create([
        'product_id' => $product->id,
        'marketplace' => 'wb',
        'external_id' => '12345',
    ]);

    $action = resolve(SyncOrdersFromMarketplaceAction::class);
    $action->execute($connection);

    $this->assertDatabaseHas('orders', [
        'organization_id' => $user->organization_id,
        'marketplace' => 'wb',
        'external_id' => $mockOrder->external_order_id,
        'status' => OrderStatusEnum::PENDING->value,
        'total_price' => 150050,
    ]);
});
