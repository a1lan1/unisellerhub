<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces\Http\Middlewares;

use App\Modules\Shared\Application\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasOrganization
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->has_organization) {
            $this->tenantManager->setOrganizationId((int) $user->organization_id);

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User does not belong to an organization.'], 403);
        }

        return back()->with('error', 'Please create an organization first.');
    }
}
