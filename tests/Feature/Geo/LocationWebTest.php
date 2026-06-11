<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Models\Location;
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

it('can display the locations page', function (): void {
    Location::factory()->count(3)->create(['user_id' => $this->user->id]);

    get(route('geo.locations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('geo/Locations')
            ->has('locations', 3)
            ->has('locations.0', fn (Assert $json): Assert => $json
                ->hasAll(['id', 'name', 'address', 'latitude', 'longitude', 'external_ids', 'type', 'reviews_count', 'reviews_avg_rating', 'user_id', 'created_at', 'updated_at'])
            )
        );
});

it('redirects unauthenticated users from locations page', function (): void {
    $this->postJson(route('logout'));
    get(route('geo.locations.index'))
        ->assertRedirect(route('login'));
});
