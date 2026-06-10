<?php

declare(strict_types=1);

use App\Modules\Activity\Domain\Interfaces\ActivityServiceInterface;
use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Order\Domain\Interfaces\OrderServiceInterface;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('can display the dashboard page with stats', function (): void {
    $mockOrderStats = ['total_orders' => 10, 'total_revenue' => 1000];
    $mockInventoryStats = ['low_stock_items' => 5, 'out_of_stock_items' => 2];
    $mockActivities = ActivityResource::collection(new Collection([
        (object) ['id' => 1, 'description' => 'Test Activity 1', 'properties' => [], 'created_at' => now()],
        (object) ['id' => 2, 'description' => 'Test Activity 2', 'properties' => [], 'created_at' => now()],
    ]));

    $this->mock(OrderServiceInterface::class)
        ->shouldReceive('getDashboardStats')
        ->once()
        ->andReturn($mockOrderStats);

    $this->mock(InventoryServiceInterface::class)
        ->shouldReceive('getInventoryHealthStats')
        ->once()
        ->andReturn($mockInventoryStats);

    $this->mock(ActivityServiceInterface::class)
        ->shouldReceive('getLatestFormattedActivitiesForUser')
        ->once()
        ->andReturn($mockActivities);

    get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('stats', $mockOrderStats)
            ->where('inventory_stats', $mockInventoryStats)
            ->has('activities', 2)
            ->has('activities.0', fn (Assert $json): Assert => $json
                ->where('id', 1)
                ->where('description', 'Test Activity 1')
                ->where('created_at', $mockActivities[0]->created_at->toDateTimeString())
                ->where('properties', [])
                ->etc()
            )
            ->has('activities.1', fn (Assert $json): Assert => $json
                ->where('id', 2)
                ->where('description', 'Test Activity 2')
                ->where('created_at', $mockActivities[1]->created_at->toDateTimeString())
                ->where('properties', [])
                ->etc()
            )
            ->where('selectedDate', now()->toDateString())
        );
});

it('can display the dashboard page with a specific date', function (): void {
    $mockOrderStats = ['total_orders' => 5, 'total_revenue' => 500];
    $mockInventoryStats = ['low_stock_items' => 1, 'out_of_stock_items' => 0];
    $mockActivities = ActivityResource::collection(new Collection([]));
    $specificDate = '2023-05-15';

    $this->mock(OrderServiceInterface::class)
        ->shouldReceive('getDashboardStats')
        ->once()
        ->withArgs(fn ($user, $date): bool => $user->is($this->user) && $date === $specificDate)
        ->andReturn($mockOrderStats);

    $this->mock(InventoryServiceInterface::class)
        ->shouldReceive('getInventoryHealthStats')
        ->once()
        ->andReturn($mockInventoryStats);

    $this->mock(ActivityServiceInterface::class)
        ->shouldReceive('getLatestFormattedActivitiesForUser')
        ->once()
        ->andReturn($mockActivities);

    get(route('dashboard', ['date' => $specificDate]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('stats', $mockOrderStats)
            ->where('inventory_stats', $mockInventoryStats)
            ->where('activities', $mockActivities->jsonSerialize())
            ->where('selectedDate', $specificDate)
        );
});

it('redirects unauthenticated users from dashboard page', function (): void {
    $this->postJson(route('logout'));
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});
