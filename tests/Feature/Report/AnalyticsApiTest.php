<?php

declare(strict_types=1);

use App\Modules\Product\Application\Actions\UpdateProductFinanceAction;
use App\Modules\Product\Domain\Data\UpdateProductFinanceData;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Cknow\Money\Money;

use function Pest\Laravel\patchJson;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can update finance data for a product listing', function (): void {
    $productListing = ProductListing::factory()->create();
    $updateData = [
        'listing_id' => $productListing->id,
        'cost_price' => 150.50,
        'commission_percent' => 10.25,
        'logistic_cost' => null,
    ];

    $this->mock(UpdateProductFinanceAction::class)
        ->shouldReceive('execute')
        ->once()
        ->withArgs(function (UpdateProductFinanceData $actualData) use ($updateData): bool {
            $expectedData = UpdateProductFinanceData::fromRequest([
                'listing_id' => $updateData['listing_id'],
                'cost_price' => $updateData['cost_price'],
                'commission_percent' => $updateData['commission_percent'],
                'logistic_cost' => $updateData['logistic_cost'],
            ]);

            return $actualData->listingId === $expectedData->listingId &&
                   $actualData->costPrice->equals($expectedData->costPrice) &&
                   abs($actualData->commissionPercent - $expectedData->commissionPercent) < 0.001 &&
                   (! $actualData->logisticCost instanceof Money && ! $expectedData->logisticCost instanceof Money);
        });

    patchJson(route('api.analytics.update_finance'), $updateData)
        ->assertOk()
        ->assertJson(['message' => 'Finance data updated successfully!']);
});

it('validates update finance data', function (): void {
    patchJson(route('api.analytics.update_finance'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing_id']);

    patchJson(route('api.analytics.update_finance'), [
        'listing_id' => 9999,
        'cost_price' => 'invalid',
        'commission_percent' => 'invalid',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['listing_id', 'cost_price', 'commission_percent']);
});

it('requires authentication to update finance data', function (): void {
    $this->postJson(route('logout'));
    $productListing = ProductListing::factory()->create();

    patchJson(route('api.analytics.update_finance'), [
        'listing_id' => $productListing->id,
        'cost_price' => 100,
    ])
        ->assertUnauthorized();
});
