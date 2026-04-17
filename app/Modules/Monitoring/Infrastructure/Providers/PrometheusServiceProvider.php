<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Infrastructure\Providers;

use App\Modules\Monitoring\Domain\Collectors\HttpMetricsCollector;
use App\Modules\Monitoring\Domain\Collectors\MarketplaceMetricsCollector;
use App\Modules\Monitoring\Domain\Collectors\ModelCountCollector;
use Illuminate\Support\ServiceProvider;
use Override;
use Spatie\Prometheus\Collectors\Horizon\CurrentMasterSupervisorCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentProcessesPerQueueCollector;
use Spatie\Prometheus\Collectors\Horizon\CurrentWorkloadCollector;
use Spatie\Prometheus\Collectors\Horizon\FailedJobsPerHourCollector;
use Spatie\Prometheus\Collectors\Horizon\HorizonStatusCollector;
use Spatie\Prometheus\Collectors\Horizon\JobsPerMinuteCollector;
use Spatie\Prometheus\Collectors\Horizon\RecentJobsCollector;
use Spatie\Prometheus\Collectors\Queue\QueueDelayedJobsCollector;
use Spatie\Prometheus\Collectors\Queue\QueueOldestPendingJobCollector;
use Spatie\Prometheus\Collectors\Queue\QueuePendingJobsCollector;
use Spatie\Prometheus\Collectors\Queue\QueueReservedJobsCollector;
use Spatie\Prometheus\Collectors\Queue\QueueSizeCollector;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->registerAppCollectors();
        $this->registerHorizonCollectors();
        $this->registerQueueCollectors(['default', 'high', 'low']);
    }

    public function registerAppCollectors(): self
    {
        Prometheus::registerCollectorClasses([
            ModelCountCollector::class,
            HttpMetricsCollector::class,
            MarketplaceMetricsCollector::class,
        ]);

        return $this;
    }

    public function registerHorizonCollectors(): self
    {
        Prometheus::registerCollectorClasses([
            CurrentMasterSupervisorCollector::class,
            CurrentProcessesPerQueueCollector::class,
            CurrentWorkloadCollector::class,
            FailedJobsPerHourCollector::class,
            HorizonStatusCollector::class,
            JobsPerMinuteCollector::class,
            RecentJobsCollector::class,
        ]);

        return $this;
    }

    public function registerQueueCollectors(array $queues = [], ?string $connection = null): self
    {
        Prometheus::registerCollectorClasses([
            QueueSizeCollector::class,
            QueuePendingJobsCollector::class,
            QueueDelayedJobsCollector::class,
            QueueReservedJobsCollector::class,
            QueueOldestPendingJobCollector::class,
        ]);

        return $this;
    }
}
