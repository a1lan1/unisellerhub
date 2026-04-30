<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Product\Domain\Models\Product;
use App\Modules\User\Domain\Models\User;

it('can search for products via scout', function (): void {
    config(['scout.driver' => 'collection']); // Use collection driver for reliable testing without Meilisearch

    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Searchable Item A',
        'sku' => 'UNIQUE-SKU-1',
    ]);

    Product::factory()->create([
        'organization_id' => $user->organization_id,
        'name' => 'Other Item B',
        'sku' => 'UNIQUE-SKU-2',
    ]);

    $response = $this->getJson(route('api.search', ['q' => 'Searchable']));

    $response->assertStatus(200)
        ->assertJsonFragment(['title' => 'Searchable Item A'])
        ->assertJsonMissing(['title' => 'Other Item B']);
});
