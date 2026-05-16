<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Domain\Interfaces\LocationServiceInterface;
use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Interfaces\Http\Requests\LocationRequest;
use App\Modules\Geo\Interfaces\Http\Resources\LocationResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function __construct(private readonly LocationServiceInterface $locationService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $locations = $this->locationService->getLocationsForUser($request->user());

        return LocationResource::collection($locations);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(LocationRequest $request): LocationResource
    {
        $this->authorize('create', Location::class);

        $location = $this->locationService->storeLocation($request->toDto());

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Location $location): LocationResource
    {
        $this->authorize('view', $location);

        $location = $this->locationService->getLocationWithStats($location);

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LocationRequest $request, Location $location): LocationResource
    {
        $this->authorize('update', $location);

        $location = $this->locationService->storeLocation($request->toDto());

        return new LocationResource($location);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Location $location): JsonResponse
    {
        $this->authorize('delete', $location);

        $this->locationService->deleteLocation($location);

        return response()->json(null, 204);
    }
}
