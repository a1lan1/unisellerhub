<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Domain\Data\SyncBulkProductData;
use App\Modules\Product\Domain\Data\SyncProductsData;
use App\Modules\Product\Interfaces\Http\Requests\Api\SyncBulkProductRequest;
use App\Modules\Product\Interfaces\Http\Requests\Api\SyncProductRequest;
use Illuminate\Http\JsonResponse;

class ProductSyncController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function sync(SyncProductRequest $request): JsonResponse
    {
        $marketplace = MarketplaceEnum::tryFrom((string) $request->input('marketplace'));

        $this->productService->syncProducts(new SyncProductsData(
            organizationId: $request->user()->organization_id,
            marketplace: $marketplace
        ));

        return response()->json(['message' => 'Sync job dispatched!']);
    }

    /**
     * Perform bulk synchronization for selected product listings.
     */
    public function syncBulk(SyncBulkProductRequest $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        $this->productService->syncBulkProducts(new SyncBulkProductData(
            organizationId: $request->user()->organization_id,
            ids: $ids
        ));

        return response()->json(['message' => 'Bulk sync job dispatched for '.count($ids).' items!']);
    }
}
