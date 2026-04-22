<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\Services;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use App\Modules\Marketplace\Domain\Data\MarketplaceConnectionStatsData;
use App\Modules\Marketplace\Domain\Data\StoreMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Data\UpdateMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Spatie\Prometheus\Facades\Prometheus;

readonly class MarketplaceConnectionService
{
    public function __construct(
        private MarketplaceConnectionRepositoryInterface $repository,
        private ActivityRepositoryInterface $activityRepository
    ) {}

    /**
     * Get all marketplace connections for a given user's organization.
     *
     * @return Collection<int, MarketplaceConnection>
     */
    public function getConnectionsForUser(User $user): Collection
    {
        return $this->repository->getConnectionsByOrganizationId((int) $user->organization_id);
    }

    /**
     * Create a new marketplace connection.
     */
    public function createConnection(StoreMarketplaceConnectionData $dto): MarketplaceConnection
    {
        return $this->repository->create($dto);
    }

    /**
     * Update a marketplace connection.
     */
    public function updateConnection(MarketplaceConnection $connection, UpdateMarketplaceConnectionData $dto): MarketplaceConnection
    {
        return $this->repository->update($connection, $dto);
    }

    /**
     * Delete a marketplace connection.
     */
    public function deleteConnection(MarketplaceConnection $connection): void
    {
        $this->repository->delete($connection);
    }

    public function getMarketplaceConnectionStats(MarketplaceConnection $connection): MarketplaceConnectionStatsData
    {
        $organization = $connection->organization;

        $totalProducts = $organization->products()
            ->whereHas('listings', fn ($q) => $q->where('marketplace', $connection->marketplace))
            ->count();

        $totalOrders = $organization->orders()
            ->where('marketplace', $connection->marketplace)
            ->count();

        Prometheus::addCounter('synced_items_total')->inc(1, [$connection->marketplace->value]);

        $recentActivity = $this->activityRepository->getLatestActivitiesForOrganization($organization->id, 10)
            ->filter(fn ($activity): bool => ($activity->properties['marketplace'] ?? null) === $connection->marketplace->value);

        return new MarketplaceConnectionStatsData(
            totalProducts: $totalProducts,
            totalOrders: $totalOrders,
            recentActivity: ActivityResource::collection($recentActivity),
        );
    }

    /**
     * Get paginated activities for a given organization and marketplace.
     *
     * @return LengthAwarePaginator<int, ActivityResource>
     */
    public function getMarketplaceConnectionLogs(MarketplaceConnection $connection, int $perPage = 20): LengthAwarePaginator
    {
        $organizationId = $connection->organization_id;

        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, Activity> $logs */
        $logs = $this->activityRepository->getActivitiesForOrganizationByMarketplace(
            $organizationId,
            $connection->marketplace->value,
            $perPage
        );

        // Transform collection and keep paginator
        return $logs->through(fn ($activity): ActivityResource => new ActivityResource($activity));
    }
}
