<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;

class SyncOrdersData
{
    public function __construct(
        public int $organizationId,
        public ?MarketplaceEnum $marketplace = null,
    ) {}
}
