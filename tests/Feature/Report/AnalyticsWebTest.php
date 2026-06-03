<?php

declare(strict_types=1);

use App\Modules\Report\Domain\Data\AbcAnalysisData;
use App\Modules\Report\Domain\Interfaces\AnalyticsServiceInterface;
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

it('can display the ABC analysis page', function (): void {
    $this->mock(AnalyticsServiceInterface::class)
        ->shouldReceive('getAbcAnalysis')
        ->once()
        ->andReturn(AbcAnalysisData::emptyAnalysis());

    get(route('analytics.abc', ['endDate' => '2023-12-31', 'days' => 30]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Analytics/Abc')
            ->has('abc') // Check for the 'abc' prop
            ->where('selectedEndDate', '2023-12-31')
            ->where('days', 30)
        );
});

it('can display the profitability analysis page', function (): void {
    $this->mock(AnalyticsServiceInterface::class)
        ->shouldReceive('getProfitabilityAnalysis')
        ->once()
        ->andReturn([]);

    get(route('analytics.profitability'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Analytics/Profitability')
            ->has('items', 0)
        );
});

it('redirects unauthenticated users from analytics pages', function (): void {
    $this->postJson(route('logout'));

    get(route('analytics.abc'))
        ->assertRedirect(route('login'));

    get(route('analytics.profitability'))
        ->assertRedirect(route('login'));
});
