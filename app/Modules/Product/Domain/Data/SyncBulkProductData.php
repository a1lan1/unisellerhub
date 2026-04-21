<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

class SyncBulkProductData
{
    /**
     * @param  int[]  $ids
     */
    public function __construct(
        public int $organizationId,
        public array $ids,
    ) {}
}
