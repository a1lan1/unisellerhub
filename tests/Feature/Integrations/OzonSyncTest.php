<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceAccount;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('syncs ozon products correctly converting strings to kopeks', function (): void {
    $clientId = 'ozon_client_'.Str::random(8);

    $connection = $this->user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::OZON,
        'name' => 'Ozon Account',
        'credentials' => ['client_id' => $clientId, 'api_key' => 'ozon-key'],
        'is_active' => true,
    ]);

    $mockAccount = MockMarketplaceAccount::factory()->create(['marketplace' => 'ozon']);
    $mockAccount->credentials()->create(['key' => 'client_id', 'value' => $clientId]);

    $externalId = '555777';

    // Ozon Mock responses
    Http::fake([
        '*/product/list' => Http::response([
            'result' => [
                'items' => [
                    ['product_id' => (int) $externalId, 'offer_id' => 'OZON-SKU-123'],
                ],
                'total' => 1,
            ],
        ]),
        '*/product/info/list' => Http::response([
            'result' => [
                'items' => [
                    [
                        'id' => (int) $externalId,
                        'offer_id' => 'OZON-SKU-123',
                        'name' => 'Ozon Product',
                        'price' => '1499.90', // Ozon returns strings
                        'images' => [],
                    ],
                ],
            ],
        ]),
    ]);

    resolve(SyncProductsFromMarketplaceAction::class)->execute($connection);

    // Verify local storage in kopeks
    $listing = ProductListing::where('external_id', $externalId)->first();
    expect($listing)->not->toBeNull()
        ->and((int) $listing->price->getAmount())->toBe(149990); // "1499.90" -> 149990

    expect($listing->product->name)->toBe('Ozon Product');
});
