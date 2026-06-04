<?php

declare(strict_types=1);

use App\Modules\User\Domain\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('can create an organization', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);

    $organizationName = 'Test Organization';

    postJson(route('api.organizations.store'), [
        'name' => $organizationName,
    ])
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll(['message', 'organization_id'])
            ->where('message', 'Organization created successfully!')
            ->whereType('organization_id', 'integer')
        );

    assertDatabaseHas('organizations', [
        'name' => $organizationName,
    ]);
});

it('cannot create an organization without a name', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);

    postJson(route('api.organizations.store'), [
        'name' => null,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('cannot create an organization with a duplicate name', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $this->actingAs($user);
    $organizationName = 'Existing Organization';

    postJson(route('api.organizations.store'), [
        'name' => $organizationName,
    ])
        ->assertStatus(201);

    postJson(route('api.organizations.store'), [
        'name' => $organizationName,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('requires authentication to create an organization', function (): void {
    postJson(route('api.organizations.store'), [
        'name' => 'Unauthorized Organization',
    ])
        ->assertStatus(401);
});
