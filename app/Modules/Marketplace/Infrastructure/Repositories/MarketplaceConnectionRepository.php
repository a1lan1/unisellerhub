<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Data\StoreMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Data\UpdateMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MarketplaceConnectionRepository implements MarketplaceConnectionRepositoryInterface
{
    public function findByMarketplaceAndCredentials(MarketplaceEnum $marketplace, string $credentialKey, string $credentialValue): ?MarketplaceConnection
    {
        // We cannot use JSON queries on encrypted columns because the database sees encrypted strings, not JSON.
        // We fetch connections for the marketplace and filter them in PHP where they are decrypted.
        /** @var MarketplaceConnection|null $connection */
        $connection = MarketplaceConnection::query()
            ->where('marketplace', $marketplace)
            ->get()
            ->first(fn (MarketplaceConnection $connection): bool => ($connection->credentials->getValue()[$credentialKey] ?? null) === $credentialValue);

        return $connection;
    }

    /**
     * @return Collection<int, MarketplaceConnection>
     */
    public function getConnectionsByOrganizationId(int $organizationId): Collection
    {
        return MarketplaceConnection::query()
            ->where('organization_id', $organizationId)
            ->get();
    }

    public function findByOrganizationAndMarketplace(int $organizationId, MarketplaceEnum $marketplace, bool $activeOnly = false): ?MarketplaceConnection
    {
        return MarketplaceConnection::query()
            ->where('organization_id', $organizationId)
            ->where('marketplace', $marketplace)
            ->when($activeOnly, fn (Builder $query) => $query->where('is_active', true))
            ->first();
    }

    public function create(StoreMarketplaceConnectionData $dto): MarketplaceConnection
    {
        return MarketplaceConnection::create([
            'organization_id' => $dto->organizationId,
            'marketplace' => $dto->marketplace,
            'name' => $dto->name,
            'credentials' => $dto->credentials,
            'is_active' => true,
        ]);
    }

    public function update(MarketplaceConnection $connection, UpdateMarketplaceConnectionData $dto): MarketplaceConnection
    {
        $connection->update($dto->toArray());

        return $connection;
    }

    public function delete(MarketplaceConnection $connection): void
    {
        $connection->delete();
    }
}
