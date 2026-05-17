<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Domain\Interfaces\LocationServiceInterface;
use App\Modules\Geo\Interfaces\Http\Resources\LocationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(private readonly LocationServiceInterface $locationService) {}

    public function index(Request $request): Response
    {
        $locations = $this->locationService->getLocationsForUser($request->user());

        return Inertia::render('geo/Locations', [
            'locations' => LocationResource::collection($locations),
        ]);
    }
}
