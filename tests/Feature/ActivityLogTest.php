<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Activity\Application\Services\ActivityLoggerService;
use App\Modules\Activity\Domain\Events\ActivityLogged;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->actingAs($this->user);
});

it('logs activity and dispatches event', function (): void {
    Event::fake([ActivityLogged::class]);

    $logger = resolve(ActivityLoggerService::class);
    $logger->log(
        organizationId: $this->user->organization_id,
        message: 'Test Activity Logged',
        type: 'success',
        properties: ['foo' => 'bar']
    );

    // Verify DB entry
    $activity = Activity::first();
    expect($activity->description)->toBe('Test Activity Logged')
        ->and($activity->properties['type'])->toBe('success')
        ->and($activity->properties['foo'])->toBe('bar')
        ->and($activity->log_name)->toBe((string) $this->user->organization_id);

    // Verify Event
    Event::assertDispatched(ActivityLogged::class, fn (ActivityLogged $event): bool => $event->organizationId === $this->user->organization_id &&
           $event->activity['description'] === 'Test Activity Logged');
});
