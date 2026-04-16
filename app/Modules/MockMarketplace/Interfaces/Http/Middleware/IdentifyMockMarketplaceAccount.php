<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Middleware;

use App\Modules\MockMarketplace\Domain\Models\MockMarketplaceCredential;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyMockMarketplaceAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');
        $clientIdHeader = $request->header('Client-Id');
        $apiKeyHeader = $request->header('Api-Key');

        $accountId = null;

        // 1. WB / MoySklad / Avito (Authorization Header)
        if ($authHeader) {
            $token = str_replace('Bearer ', '', $authHeader);
            $credential = MockMarketplaceCredential::where('value', $token)
                ->whereIn('key', ['token', 'ms_token', 'client_id']) // Avito mock uses client_id as token
                ->first();
            $accountId = $credential?->mock_marketplace_account_id;
        }
        // 2. Ozon (Client-Id Header)
        elseif ($clientIdHeader) {
            $credential = MockMarketplaceCredential::where('key', 'client_id')
                ->where('value', $clientIdHeader)
                ->first();
            $accountId = $credential?->mock_marketplace_account_id;
        }
        // 3. Yandex (Api-Key Header)
        elseif ($apiKeyHeader) {
            $credential = MockMarketplaceCredential::where('key', 'api_key')
                ->where('value', $apiKeyHeader)
                ->first();
            $accountId = $credential?->mock_marketplace_account_id;
        }

        if (! $accountId) {
            return response()->json(['error' => 'Unauthorized mock account'], 401);
        }

        $request->attributes->set('mock_account_id', $accountId);

        return $next($request);
    }
}
