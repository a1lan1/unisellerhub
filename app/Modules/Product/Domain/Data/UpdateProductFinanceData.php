<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use Cknow\Money\Money;

class UpdateProductFinanceData
{
    public function __construct(
        public int $listingId,
        public ?Money $costPrice = null,
        public ?float $commissionPercent = null,
        public ?Money $logisticCost = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            listingId: (int) $data['listing_id'],
            costPrice: isset($data['cost_price']) ? Money::RUB((int) round((float) $data['cost_price'] * 100)) : null,
            commissionPercent: isset($data['commission_percent']) ? (float) $data['commission_percent'] : null,
            logisticCost: isset($data['logistic_cost']) ? Money::RUB((int) round((float) $data['logistic_cost'] * 100)) : null,
        );
    }
}
