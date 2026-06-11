<?php

declare(strict_types=1);

use App\Modules\PriceAnalysis\Infrastructure\Jobs\InitiatePriceAnalysisReportJob;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Bus::fake();
    $this->user = User::factory()->withBaseRoles()->create();
    $this->organization = Organization::factory()->create();
    $this->user->organization_id = $this->organization->id;
    $this->user->save();
    $this->actingAs($this->user);
});

it('dispatches InitiatePriceAnalysisReportJob', function (): void {
    postJson(route('api.price-analysis.analyze'))
        ->assertOk()
        ->assertJson(['message' => 'Price analysis report generation job dispatched!']);

    Bus::assertDispatched(
        InitiatePriceAnalysisReportJob::class,
        fn (InitiatePriceAnalysisReportJob $job): bool => $job->organizationId === $this->organization->id && $job->userId === $this->user->id
    );
});

it('requires authentication to dispatch price analysis job', function (): void {
    $this->postJson(route('logout'));

    postJson(route('api.price-analysis.analyze'))
        ->assertUnauthorized();
});
