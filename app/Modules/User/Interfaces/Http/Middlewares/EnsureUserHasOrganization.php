<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // We check that the user is authenticated and has an organization
        if ($user && $user->organization_id !== null) {
            return $next($request);
        }

        // If the organization does not exist, we return an error
        if ($request->expectsJson()) {
            return response()->json(['message' => 'User does not belong to an organization.'], 403);
        }

        // For Inertia requests, we redirect and show an error
        return back()->with('error', 'Please create an organization first.');
    }
}
