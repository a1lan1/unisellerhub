<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Domain\Collectors;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use Spatie\Prometheus\Collectors\Collector;
use Spatie\Prometheus\Facades\Prometheus;

class MarketplaceMetricsCollector implements Collector
{
    public function register(): void
    {
        /*
         * Gauge: Remaining API limits.
         * For Gauges without a direct 'set' method, we use the Pull pattern with cache.
         */
        Prometheus::addGauge('api_rate_limit_remaining')
            ->helpText('Remaining API rate limit for specific marketplace')
            ->label('marketplace')
            ->value(fn () => MarketplaceConnection::all()->map(function ($conn): array {
                $limit = cache('prometheus.api_limit.'.$conn->marketplace->value, 0);

                return [$limit, [$conn->marketplace->value]];
            })->all());

        /*
         * Counter: Total sync errors.
         * We just register it here. Use ->inc() in your code to update.
         */
        Prometheus::addCounter('sync_errors_total')
            ->helpText('Total number of marketplace synchronization errors')
            ->label('marketplace')
            ->label('type');

        /*
         * Counters for sync duration (Sum and Count).
         * Use ->inc($seconds) and ->inc(1) in your code.
         */
        Prometheus::addCounter('sync_duration_seconds_sum')
            ->helpText('Sum of marketplace synchronization durations in seconds')
            ->label('marketplace')
            ->label('operation');

        Prometheus::addCounter('sync_duration_seconds_count')
            ->helpText('Count of marketplace synchronization operations')
            ->label('marketplace')
            ->label('operation');

        /*
         * Counter: Total items processed/synced.
         */
        Prometheus::addCounter('synced_items_total')
            ->helpText('Total number of items successfully synced from marketplace')
            ->label('marketplace');
    }
}
