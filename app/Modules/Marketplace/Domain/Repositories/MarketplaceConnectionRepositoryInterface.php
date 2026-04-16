<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Data\StoreMarketplaceConnectionData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use Illuminate\Support\Collection;

interface MarketplaceConnectionRepositoryInterface
{
    public function findByMarketplaceAndCredentials(MarketplaceEnum $marketplace, string $credentialKey, string $credentialValue): ?MarketplaceConnection;

    /**
     * @return Collection<int, MarketplaceConnection>
     */
    public function getConnectionsByOrganizationId(int $organizationId): Collection;

    public function findByOrganizationAndMarketplace(int $organizationId, MarketplaceEnum $marketplace): ?MarketplaceConnection;

    public function create(StoreMarketplaceConnectionData $dto): MarketplaceConnection;

    public function delete(MarketplaceConnection $connection): void;
}
