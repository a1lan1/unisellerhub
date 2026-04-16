<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MockIdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only for state-changing requests
        if (! $request->isMethodSafe()) {
            $key = $request->header('X-Idempotency-Key');

            if ($key) {
                $cacheKey = 'mock_idemp:'.$key;

                if (Cache::has($cacheKey)) {
                    $cachedData = Cache::get($cacheKey);

                    return response()->json($cachedData['content'], $cachedData['status']);
                }

                $response = $next($request);

                // Cache successful or client-error responses, but not server errors
                if ($response->status() < 500) {
                    Cache::put($cacheKey, [
                        'content' => json_decode((string) $response->getContent(), true),
                        'status' => $response->status(),
                    ], now()->addHour());
                }

                return $response;
            }
        }

        return $next($request);
    }
}
