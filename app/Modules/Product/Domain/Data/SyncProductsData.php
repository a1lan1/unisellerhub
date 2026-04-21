<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;

class SyncProductsData
{
    public function __construct(
        public int $organizationId,
        public ?MarketplaceEnum $marketplace = null,
    ) {}
}
