<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Interfaces;

use App\Modules\Activity\Interfaces\Http\Resources\ActivityResource;
use App\Modules\Marketplace\Domain\Data\MarketplaceConnectionStatsData;
use App\Modules\Marketplace\Domain\Data\StoreMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Data\UpdateMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MarketplaceConnectionServiceInterface
{
    /**
     * Get all marketplace connections for a given user's organization.
     *
     * @return Collection<int, MarketplaceConnection>
     */
    public function getConnectionsForUser(User $user): Collection;

    /**
     * Create a new marketplace connection.
     */
    public function createConnection(StoreMarketplaceConnectionData $dto): MarketplaceConnection;

    /**
     * Update a marketplace connection.
     */
    public function updateConnection(MarketplaceConnection $connection, UpdateMarketplaceConnectionData $dto): MarketplaceConnection;

    /**
     * Delete a marketplace connection.
     */
    public function deleteConnection(MarketplaceConnection $connection): void;

    public function getMarketplaceConnectionStats(MarketplaceConnection $connection): MarketplaceConnectionStatsData;

    /**
     * Get paginated activities for a given organization and marketplace.
     *
     * @return LengthAwarePaginator<int, ActivityResource>
     */
    public function getMarketplaceConnectionLogs(MarketplaceConnection $connection, int $perPage = 20): LengthAwarePaginator;
}
