<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Interfaces\SellerServiceInterface;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can display the seller details page', function (): void {
    $seller = User::factory()->create(['organization_id' => $this->organization->id]);

    $this->mock(SellerServiceInterface::class)
        ->shouldReceive('getSellerWithProducts')
        ->once()
        ->andReturn($seller); // Return the seller model

    get(route('sellers.show', $seller))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('geo/Reviews')
            ->has('seller', fn (Assert $json): Assert => $json
                ->where('id', $seller->id)
                ->where('name', $seller->name)
                ->etc()
            )
        );
});

it('redirects unauthenticated users from seller details page', function (): void {
    $seller = User::factory()->create(['organization_id' => $this->organization->id]);
    $this->postJson(route('logout'));
    get(route('sellers.show', $seller))
        ->assertRedirect(route('login'));
});
