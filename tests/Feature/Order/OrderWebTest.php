<?php

declare(strict_types=1);

use App\Modules\Order\Domain\Models\Order;
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

it('can display the orders page', function (): void {
    $orders = Order::factory()->count(3)->create(['organization_id' => $this->organization->id]);

    get(route('orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Orders/Index')
            ->has('orders.data', 3)
            ->has('orders.data.0', fn (Assert $json): Assert => $json
                ->hasAll(['id', 'marketplace', 'status', 'total_price', 'order_date', 'external_id', 'formatted_total_price', 'items'])
                ->whereType('order_date', 'string')
                ->whereType('items', 'array')
            )
            ->has('filters')
        );
});

it('redirects unauthenticated users from orders page', function (): void {
    $this->postJson(route('logout'));
    get(route('orders.index'))
        ->assertRedirect(route('login'));
});
