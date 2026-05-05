<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Carbon\CarbonInterface;
use Cknow\Money\Money;
use Override;
use Spatie\LaravelData\Data;

class ProductListingStoreData extends Data
{
    public function __construct(
        public int $productId,
        public MarketplaceEnum $marketplace,
        public string $external_id,
        public string $vendor_code,
        public CarbonInterface $lastSyncedAt,
        public Money $price,
        public ?Money $old_price = null,
        public ?float $discount = null,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'vendor_code' => $this->vendor_code,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'marketplace' => $this->marketplace,
            'external_id' => $this->external_id,
            'status' => 'active',
            'last_synced_at' => $this->lastSyncedAt,
        ];
    }
}
