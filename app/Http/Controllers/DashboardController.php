<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Activity\Application\Services\ActivityService;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Order\Application\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly InventoryService $inventoryService,
        private readonly ActivityService $activityService
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->orderService->getDashboardStats($request->user()),
            'inventory_stats' => $this->inventoryService->getInventoryHealthStats($request->user()),
            'activities' => $this->activityService->getLatestFormattedActivitiesForUser($request->user()),
        ]);
    }
}
