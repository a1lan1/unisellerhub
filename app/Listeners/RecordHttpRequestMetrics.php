<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Http\Request;
use Laravel\Octane\Events\RequestTerminated;
use Spatie\Prometheus\Facades\Prometheus;
use Symfony\Component\HttpFoundation\Response;

class RecordHttpRequestMetrics
{
    public function handle(RequestTerminated $event): void
    {
        $duration = microtime(true) - $event->request->server('REQUEST_TIME_FLOAT', microtime(true));
        $method = $event->request->method();

        if ($this->shouldIgnore($event->request)) {
            return;
        }

        $uri = $this->getUri($event->request);
        $status = $event->response->getStatusCode();
        $labels = [$method, $uri, $status];

        Prometheus::addCounter('http_requests_total')->inc(1, $labels);
        Prometheus::addCounter('http_requests_duration_seconds_sum')->inc($duration, $labels);
        Prometheus::addCounter('http_requests_duration_seconds_count')->inc(1, $labels);

        // Increment page views counter for successful GET HTML responses
        if ($this->isSuccessfulHtmlGet($event->request, $event->response)) {
            $routeName = $event->request->route()?->getName() ?: $uri;
            Prometheus::addCounter('page_views_total')->inc(1, [$routeName]);
        }
    }

    protected function getUri(Request $request): string
    {
        // Prefer route name to limit label cardinality
        $routeName = $request->route()?->getName();
        if ($routeName) {
            return $routeName;
        }

        // Exclude /metrics from being recorded as a high-cardinality URI
        if ($request->is(config('prometheus.urls.default'))) {
            return '/metrics';
        }

        return '/'.ltrim($request->path(), '/');
    }

    protected function shouldIgnore(Request $request): bool
    {
        $routeName = $request->route()?->getName() ?? '';

        // Ignore Prometheus scrape endpoint (not part of app traffic)
        if ($routeName === 'prometheus.default') {
            return true;
        }

        $metricsPath = trim((string) config('prometheus.urls.default'), '/');
        if ($metricsPath !== '' && $request->is($metricsPath)) {
            return true;
        }

        if ($request->is('prometheus*')) {
            return true;
        }

        // Ignore static/noisy assets
        $path = ltrim($request->path(), '/');
        if ($path === 'favicon.ico' || $path === 'robots.txt') {
            return true;
        }

        return str_starts_with($path, 'build/') || str_starts_with($path, 'assets/') || str_starts_with($path, 'images/');
    }

    protected function isSuccessfulHtmlGet(Request $request, Response $response): bool
    {
        if (strtoupper($request->method()) !== 'GET') {
            return false;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');

        return str_contains((string) $contentType, 'text/html');
    }
}
