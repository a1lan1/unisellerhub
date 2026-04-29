<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('syncs products from moysklad correctly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $token = 'ms_token_'.Str::random(20);

    $connection = $user->organization->marketplaceConnections()->create([
        'marketplace' => MarketplaceEnum::MOYSKLAD,
        'name' => 'MS Account',
        'credentials' => ['ms_token' => $token],
        'is_active' => true,
    ]);

    $externalId = (string) Str::uuid();

    Http::fake([
        '*/entity/assortment' => Http::response([
            'rows' => [
                [
                    'id' => $externalId,
                    'article' => 'MS-ART-001',
                    'name' => 'MS Product',
                    'salePrices' => [
                        ['value' => 250000],
                    ],
                ],
            ],
        ]),
    ]);

    resolve(SyncProductsFromMarketplaceAction::class)->execute($connection);

    expect(Product::where('sku', 'MS-ART-001')->exists())->toBeTrue()
        ->and(Product::where('sku', 'MS-ART-001')->first()->name)->toBe('MS Product');
});
