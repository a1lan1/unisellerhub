<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Domain\Collectors;

use Spatie\Prometheus\Collectors\Collector;
use Spatie\Prometheus\Facades\Prometheus;

class HttpMetricsCollector implements Collector
{
    public function register(): void
    {
        Prometheus::addCounter('http_requests_total')
            ->label('method')
            ->label('uri')
            ->label('status_code')
            ->helpText('Total HTTP requests');

        // Duration metrics are represented via sum+count gauges sourced from cache
        Prometheus::addCounter('http_requests_duration_seconds_sum')
            ->label('method')
            ->label('uri')
            ->label('status_code')
            ->helpText('Sum of HTTP request durations in seconds');

        Prometheus::addCounter('http_requests_duration_seconds_count')
            ->label('method')
            ->label('uri')
            ->label('status_code')
            ->helpText('Count of HTTP requests for duration calculation');

        // Popular demo metrics
        Prometheus::addCounter('page_views_total')
            ->label('route')
            ->helpText('Total page views (successful GET requests rendered as HTML)');
    }
}
