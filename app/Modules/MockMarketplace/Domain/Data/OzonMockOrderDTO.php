<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Data;

use Spatie\LaravelData\Data;

class OzonMockOrderDTO extends Data
{
    public function __construct(
        public string $posting_number,
        public string $status,
        public string $in_process_at,
        public array $products,
    ) {}
}
