<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');

        if (! $key) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['idempotency_error' => 'Idempotency-Key header is required. Please refresh the page.']);
            }

            return response()->json(['error' => 'Idempotency-Key header is required'], 400);
        }

        $cacheKey = $this->getCacheKey($request);

        if ($cached = Cache::get($cacheKey)) {
            return $this->restoreResponse($cached);
        }

        // Increase lock time for potentially long-running operations like sync jobs (e.g., 10 minutes)
        $lock = Cache::lock($cacheKey.':lock', 600);

        if (! $lock->get()) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['idempotency_error' => 'Request is being processed. Please wait.']);
            }

            return response()->json(['error' => 'Request is being processed'], 409);
        }

        try {
            $response = $next($request);

            $this->cacheResponse($cacheKey, $response);

            return $response;
        } finally {
            $lock->release();
        }
    }

    private function shouldSkip(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    private function getCacheKey(Request $request): string
    {
        $key = $request->header('Idempotency-Key');
        $userId = $request->user()->id;

        return sprintf('idempotency:%s:%s', $userId, $key);
    }

    private function restoreResponse(array $cached): Response
    {
        // Handle Redirects
        if (isset($cached['headers']['Location'])) {
            return redirect($cached['headers']['Location'])
                ->header('Idempotent-Replayed', 'true');
        }

        // Handle JSON/Content
        return response()->json($cached['data'], $cached['status'])
            ->header('Idempotent-Replayed', 'true');
    }

    private function cacheResponse(string $key, Response $response): void
    {
        // We only cache successful responses or redirects to avoid caching temporary server errors
        if ($response->getStatusCode() >= 500) {
            return;
        }

        $data = null;
        $headers = [];

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
        } elseif ($response instanceof RedirectResponse) {
            $headers['Location'] = $response->getTargetUrl();
        } else {
            $content = $response->getContent();
            $data = json_decode($content, true) ?? $content;
        }

        Cache::put($key, [
            'data' => $data,
            'status' => $response->getStatusCode(),
            'headers' => $headers,
        ], now()->addHours(24));
    }
}
