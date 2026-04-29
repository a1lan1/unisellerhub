<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Product\Domain\Actions\SyncProductsFromMarketplaceAction;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Http;

it('handles 401 unauthorized from wildberries gracefully', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $connection = MarketplaceConnection::factory()->create([
        'organization_id' => $user->organization_id,
        'marketplace' => 'wb',
    ]);

    Http::fake([
        '*/content/v2/get/cards/list' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $action = resolve(SyncProductsFromMarketplaceAction::class);

    $action->execute($connection);

    // No products should be created
    expect(Product::count())->toBe(0);
});
