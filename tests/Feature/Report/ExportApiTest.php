<?php

declare(strict_types=1);

use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use App\Modules\Report\Infrastructure\Jobs\ExportInventoryJob;
use App\Modules\Report\Infrastructure\Jobs\ExportOrdersJob;
use App\Modules\Report\Infrastructure\Jobs\GenerateAnalyticsReportJob;
use App\Modules\Shared\Domain\ValueObjects\DateRange;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
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

it('dispatches ExportOrdersJob', function (): void {
    $requestData = [
        'date_from' => '2026-01-01',
        'date_to' => '2026-01-31',
        'per_page' => 15,
        'page' => 1,
    ];

    postJson(route('exports.orders'), $requestData)
        ->assertOk()
        ->assertJson(['message' => 'Orders export started. You will be notified when the file is ready.']);

    Bus::assertDispatched(ExportOrdersJob::class, fn (ExportOrdersJob $job): bool => $job->user->id === $this->user->id &&
        $job->filters instanceof OrderFilterData &&
        $job->filters->pagination instanceof Pagination &&
        $job->filters->pagination->getPerPage() === $requestData['per_page'] &&
        $job->filters->pagination->getPage() === $requestData['page'] &&
        $job->filters->dateRange instanceof DateRange &&
        $job->filters->dateRange->from->format('Y-m-d') === $requestData['date_from'] &&
        $job->filters->dateRange->to->format('Y-m-d') === $requestData['date_to']
    );
});

it('dispatches ExportInventoryJob', function (): void {
    $requestData = ['marketplace' => 'ozon'];

    postJson(route('exports.inventory'), $requestData)
        ->assertOk()
        ->assertJson(['message' => 'Inventory export started. You will be notified when the file is ready.']);

    Bus::assertDispatched(ExportInventoryJob::class, fn (ExportInventoryJob $job): bool => $job->user->id === $this->user->id &&
           $job->filters->marketplace->value === $requestData['marketplace']);
});

it('dispatches GenerateAnalyticsReportJob', function (): void {
    $requestData = [
        'report_type' => ReportTypeEnum::ABC_ANALYSIS->value,
        'days' => 30,
        'end_date' => now()->format('Y-m-d'),
    ];

    postJson(route('exports.analytics'), $requestData)
        ->assertOk()
        ->assertJson(['message' => 'Analytics report generation started. You will be notified when the file is ready.']);

    Bus::assertDispatched(GenerateAnalyticsReportJob::class, fn (GenerateAnalyticsReportJob $job): bool => $job->user->id === $this->user->id &&
        $job->reportData->reportType === ReportTypeEnum::ABC_ANALYSIS &&
        $job->reportData->days === $requestData['days'] &&
        $job->reportData->endDate === $requestData['end_date']
    );
});

it('validates export orders data', function (): void {
    postJson(route('exports.orders'), [])
        ->assertOk();
});

it('validates export inventory data', function (): void {
    postJson(route('exports.inventory'), [])
        ->assertOk();
});

it('validates generate analytics report data', function (): void {
    postJson(route('exports.analytics'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['report_type']);

    postJson(route('exports.analytics'), [
        'report_type' => 'invalid',
        'days' => 'invalid',
        'end_date' => 'invalid',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['report_type', 'days', 'end_date']);
});

it('requires authentication for export actions', function (): void {
    $this->postJson(route('logout'));

    postJson(route('exports.orders'))
        ->assertUnauthorized();

    postJson(route('exports.inventory'))
        ->assertUnauthorized();

    postJson(route('exports.analytics'))
        ->assertUnauthorized();
});
