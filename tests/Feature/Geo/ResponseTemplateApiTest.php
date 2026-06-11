<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can list response templates', function (): void {
    ResponseTemplate::factory()->count(3)->create(['user_id' => $this->user->id]);

    getJson(route('api.geo.response-templates.index'))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->has(3)
            ->first(fn (AssertableJson $json): AssertableJson => $json->hasAll(['id', 'title', 'body', 'created_at'])
            )
        );
});

it('can create a response template', function (): void {
    $templateData = [
        'title' => 'Test Template Title',
        'body' => 'Hello, {name}!',
    ];

    postJson(route('api.geo.response-templates.store'), $templateData)
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll(['id', 'title', 'body', 'created_at'])
            ->where('title', $templateData['title'])
            ->where('body', $templateData['body'])
        );

    assertDatabaseHas('response_templates', [
        'title' => $templateData['title'],
        'body' => $templateData['body'],
        'user_id' => $this->user->id,
    ]);
});

it('can show a specific response template', function (): void {
    $template = ResponseTemplate::factory()->create(['user_id' => $this->user->id]);

    getJson(route('api.geo.response-templates.show', $template))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll(['id', 'title', 'body', 'created_at'])
            ->where('id', $template->id)
            ->where('title', $template->title)
        );
});

it('can update a response template', function (): void {
    $template = ResponseTemplate::factory()->create(['user_id' => $this->user->id]);
    $updatedData = [
        'title' => 'Updated Template Title',
        'body' => 'Updated template: {message}',
    ];

    putJson(route('api.geo.response-templates.update', $template), $updatedData)
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll(['id', 'title', 'body', 'created_at'])
            ->where('id', $template->id)
            ->where('title', $updatedData['title'])
            ->where('body', $updatedData['body'])
        );

    assertDatabaseHas('response_templates', [
        'id' => $template->id,
        'title' => $updatedData['title'],
        'body' => $updatedData['body'],
    ]);
});

it('can delete a response template', function (): void {
    $template = ResponseTemplate::factory()->create(['user_id' => $this->user->id]);

    deleteJson(route('api.geo.response-templates.destroy', $template))
        ->assertStatus(204);

    assertDatabaseMissing('response_templates', [
        'id' => $template->id,
    ]);
});

it("cannot access another organization's response template", function (): void {
    $anotherUser = User::factory()->withBaseRoles()->create();
    $anotherTemplate = ResponseTemplate::factory()->create(['user_id' => $anotherUser->id]);

    getJson(route('api.geo.response-templates.show', $anotherTemplate))
        ->assertForbidden();

    putJson(route('api.geo.response-templates.update', $anotherTemplate), ['title' => 'Attempted Update', 'body' => 'Some body content'])
        ->assertForbidden();

    deleteJson(route('api.geo.response-templates.destroy', $anotherTemplate))
        ->assertForbidden();
});

it('validates response template creation data', function (): void {
    postJson(route('api.geo.response-templates.store'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'body']);

    postJson(route('api.geo.response-templates.store'), [
        'title' => 'Valid Title',
        'body' => null,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['body']);
});
