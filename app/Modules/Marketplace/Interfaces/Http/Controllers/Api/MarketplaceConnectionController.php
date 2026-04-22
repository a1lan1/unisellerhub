<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Application\Services\MarketplaceConnectionService;
use App\Modules\Marketplace\Domain\Data\StoreMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Data\UpdateMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\DestroyMarketplaceConnectionRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\StoreMarketplaceConnectionRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\UpdateMarketplaceConnectionRequest;
use App\Modules\Marketplace\Interfaces\Http\Resources\MarketplaceConnectionResource;
use App\Modules\User\Domain\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketplaceConnectionController extends Controller
{
    public function __construct(private readonly MarketplaceConnectionService $service) {}

    /**
     * Display a listing of marketplace connections for the organization.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $connections = $this->service->getConnectionsForUser($user);

        return MarketplaceConnectionResource::collection($connections);
    }

    /**
     * Store a newly created marketplace connection.
     */
    public function store(StoreMarketplaceConnectionRequest $request): JsonResponse
    {
        $connection = $this->service->createConnection(StoreMarketplaceConnectionData::fromRequest(
            $request->validated(),
            (int) $request->user()->organization_id
        ));

        return response()->json([
            'message' => 'Connection created successfully!',
            'data' => new MarketplaceConnectionResource($connection),
        ], 201);
    }

    /**
     * Update the specified marketplace connection.
     */
    public function update(UpdateMarketplaceConnectionRequest $request, MarketplaceConnection $marketplaceConnection): JsonResponse
    {
        $connection = $this->service->updateConnection(
            $marketplaceConnection,
            UpdateMarketplaceConnectionData::fromRequest($request->validated())
        );

        return response()->json([
            'message' => 'Connection updated successfully!',
            'data' => new MarketplaceConnectionResource($connection),
        ]);
    }

    /**
     * Remove the specified marketplace connection.
     */
    public function destroy(DestroyMarketplaceConnectionRequest $request, MarketplaceConnection $marketplaceConnection): JsonResponse
    {
        $this->service->deleteConnection($marketplaceConnection);

        return response()->json(null, 204);
    }
}
