<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Activity\Domain\Interfaces\ActivityServiceInterface;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Order\Domain\Interfaces\OrderServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
        private readonly InventoryServiceInterface $inventoryService,
        private readonly ActivityServiceInterface $activityService
    ) {}

    public function index(Request $request): Response
    {
        $selectedDate = $request->input('date', now()->toDateString());

        return Inertia::render('Dashboard', [
            'stats' => $this->orderService->getDashboardStats($request->user(), $selectedDate),
            'inventory_stats' => $this->inventoryService->getInventoryHealthStats($request->user()),
            'activities' => $this->activityService->getLatestFormattedActivitiesForUser($request->user()),
            'selectedDate' => $selectedDate,
        ]);
    }
}
