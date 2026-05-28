<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\ValueObjects\Credentials;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceConnectionName;

class StoreMarketplaceConnectionData
{
    public function __construct(
        public int $organizationId,
        public MarketplaceEnum $marketplace,
        public MarketplaceConnectionName $name,
        public Credentials $credentials,
    ) {}

    public static function fromRequest(array $data, int $organizationId): self
    {
        return new self(
            organizationId: $organizationId,
            marketplace: MarketplaceEnum::from((string) $data['marketplace']),
            name: new MarketplaceConnectionName((string) $data['name']),
            credentials: new Credentials((array) $data['credentials']),
        );
    }
}
