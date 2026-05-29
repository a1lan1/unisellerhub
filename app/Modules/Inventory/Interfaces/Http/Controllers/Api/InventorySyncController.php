<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Data\PullBulkInventoryData;
use App\Modules\Inventory\Domain\Data\PullInventoryData;
use App\Modules\Inventory\Domain\Data\SyncMoySkladStockData;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Inventory\Interfaces\Http\Requests\Api\PullBulkInventoryRequest;
use App\Modules\Inventory\Interfaces\Http\Requests\Api\PullInventoryRequest;
use App\Modules\Inventory\Interfaces\Http\Requests\UpdateInventoryRequest;
use App\Modules\Inventory\Interfaces\Http\Resources\InventoryResource;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventorySyncController extends Controller
{
    public function __construct(private readonly InventoryServiceInterface $inventoryService) {}

    /**
     * Pull stocks from marketplaces.
     */
    public function pull(PullInventoryRequest $request): JsonResponse
    {
        $marketplace = MarketplaceEnum::tryFrom((string) $request->input('marketplace'));

        $this->inventoryService->pullInventory(new PullInventoryData(
            organizationId: $request->user()->organization_id,
            marketplace: $marketplace
        ));

        return response()->json(['message' => 'Inventory pull job dispatched!']);
    }

    /**
     * Pull stocks bulk for selected inventory items.
     */
    public function pullBulk(PullBulkInventoryRequest $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        $this->inventoryService->pullBulkInventory(new PullBulkInventoryData(
            organizationId: $request->user()->organization_id,
            ids: $ids
        ));

        return response()->json(['message' => 'Bulk pull job dispatched for '.count($ids).' items!']);
    }

    /**
     * Update local stock and push to marketplace.
     */
    public function update(UpdateInventoryRequest $request): InventoryResource
    {
        $inventory = $this->inventoryService->updateInventoryAndPushToMarketplace(
            (int) $request->validated('id'),
            (int) $request->validated('quantity')
        );

        return new InventoryResource($inventory);
    }

    /**
     * Sync stocks from MoySklad to all connected marketplaces.
     */
    public function syncMoySkladStock(Request $request): JsonResponse
    {
        $this->inventoryService->syncMoySkladStock(new SyncMoySkladStockData(
            organizationId: $request->user()->organization_id
        ));

        return response()->json(['message' => 'MoySklad stock sync job dispatched!']);
    }
}
