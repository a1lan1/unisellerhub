<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;

class PullInventoryData
{
    public function __construct(
        public int $organizationId,
        public ?MarketplaceEnum $marketplace = null,
    ) {}
}
