<?php

declare(strict_types=1);

use App\Modules\User\Domain\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->actingAs($this->user);
});

function createNotification(User $user, bool $read = false): DatabaseNotification
{
    return DatabaseNotification::query()->create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\SomeNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => ['message' => 'Test notification'],
        'read_at' => $read ? now() : null,
    ]);
}

it('can get a list of notifications', function (): void {
    createNotification($this->user);
    createNotification($this->user);
    createNotification($this->user);
    createNotification(User::factory()->withBaseRoles()->create());

    getJson(route('api.notifications.index'))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json): AssertableJson => $json->hasAll(['notifications', 'unread_count'])
            ->has('notifications', 3)
            ->where('unread_count', 3)
        );
});

it('can mark all notifications as read', function (): void {
    createNotification($this->user);
    createNotification($this->user);
    createNotification($this->user);

    postJson(route('api.notifications.read_all'))
        ->assertOk()
        ->assertJson(['status' => 'success']);

    $this->assertDatabaseCount('notifications', 3);
    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'read_at' => null,
    ]);
});

it('can mark a specific notification as read', function (): void {
    $notification = createNotification($this->user, false);

    postJson(route('api.notifications.read', ['id' => $notification->id]))
        ->assertOk()
        ->assertJson(['status' => 'success']);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
    ]);

    $this->assertNotNull($notification->fresh()->read_at);
});

it('can delete a specific notification', function (): void {
    $notification = createNotification($this->user);

    deleteJson(route('api.notifications.destroy', ['id' => $notification->id]))
        ->assertOk()
        ->assertJson(['status' => 'success']);

    $this->assertDatabaseMissing('notifications', [
        'id' => $notification->id,
    ]);
});

it('can clear all notifications', function (): void {
    createNotification($this->user);
    createNotification($this->user);
    createNotification($this->user);

    deleteJson(route('api.notifications.clear_all'))
        ->assertOk()
        ->assertJson(['status' => 'success']);

    $this->assertDatabaseCount('notifications', 0);
});

it('requires authentication for notification actions', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.notifications.read_all'))
        ->assertUnauthorized();

    deleteJson(route('api.notifications.clear_all'))
        ->assertUnauthorized();
});
