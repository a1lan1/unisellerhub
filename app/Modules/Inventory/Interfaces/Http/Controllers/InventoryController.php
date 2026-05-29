<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Inventory\Interfaces\Http\Requests\InventoryListingsRequest;
use App\Modules\Inventory\Interfaces\Http\Resources\InventoryResource;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryServiceInterface $inventoryService) {}

    public function index(InventoryListingsRequest $request): Response
    {
        $filter = $request->toDto();

        $paginator = $this->inventoryService->getPaginatedInventory($request->user(), $filter);

        return Inertia::render('Inventory/Index', [
            'inventory' => InventoryResource::collection($paginator),
            'filters' => $filter->toArray(),
        ]);
    }
}
