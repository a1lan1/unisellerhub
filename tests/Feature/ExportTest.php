<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Report\Infrastructure\Jobs\ExportInventoryJob;
use App\Modules\Report\Infrastructure\Jobs\ExportOrdersJob;
use App\Modules\Report\Infrastructure\Jobs\GenerateAnalyticsReportJob;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->user = User::factory()->withBaseRoles()->create();
    $this->actingAs($this->user);
});

it('can export orders to excel', function (): void {
    Queue::fake();

    $response = $this->post(route('exports.orders'));

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Orders export started. You will be notified when the file is ready.']);

    Queue::assertPushed(ExportOrdersJob::class);
});

it('can export inventory to excel', function (): void {
    Queue::fake();

    $response = $this->post(route('exports.inventory'));

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Inventory export started. You will be notified when the file is ready.']);

    Queue::assertPushed(ExportInventoryJob::class);
});

it('can generate product revenue analytics report', function (): void {
    Queue::fake();

    $response = $this->post(route('exports.analytics'), [
        'report_type' => 'product_revenue_analysis',
        'days' => 30,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Analytics report generation started. You will be notified when the file is ready.']);

    Queue::assertPushed(GenerateAnalyticsReportJob::class);
});

it('can generate product profitability analytics report', function (): void {
    Queue::fake();

    $response = $this->post(route('exports.analytics'), [
        'report_type' => 'product_profitability_analysis',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Analytics report generation started. You will be notified when the file is ready.']);

    Queue::assertPushed(GenerateAnalyticsReportJob::class);
});
