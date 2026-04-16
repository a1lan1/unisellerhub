<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;

class MockPerformanceMiddleware
{
    /**
     * @throws RandomException
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Latency Simulation (500ms - 1500ms)
        if (! app()->runningUnitTests()) {
            Sleep::usleep(random_int(500, 1500) * 1000);
        }

        // 2. Rate Limiting (Simple 60 requests per minute per mock account)
        $accountId = $request->attributes->get('mock_account_id');
        if ($accountId) {
            $executed = RateLimiter::attempt(
                'mock_api:'.$accountId,
                60,
                function (): void {}
            );

            if (! $executed) {
                return response()->json([
                    'error' => 'Too Many Requests',
                    'message' => 'Rate limit exceeded for this mock account.',
                ], 429);
            }
        }

        return $next($request);
    }
}
