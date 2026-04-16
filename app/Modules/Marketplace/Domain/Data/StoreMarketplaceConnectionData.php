<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;

class StoreMarketplaceConnectionData
{
    public function __construct(
        public int $organizationId,
        public MarketplaceEnum $marketplace,
        public string $name,
        public array $credentials,
    ) {}

    public static function fromRequest(array $data, int $organizationId): self
    {
        return new self(
            organizationId: $organizationId,
            marketplace: MarketplaceEnum::from((string) $data['marketplace']),
            name: (string) $data['name'],
            credentials: (array) $data['credentials'],
        );
    }
}
