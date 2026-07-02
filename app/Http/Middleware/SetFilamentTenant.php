<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\Shared\Application\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentTenant
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->organization_id !== null) {
            resolve(TenantManager::class)->setOrganizationId((int) $user->organization_id);
        }

        return $next($request);
    }
}
