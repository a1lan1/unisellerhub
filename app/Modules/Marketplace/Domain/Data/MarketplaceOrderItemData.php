<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use App\Modules\Product\ValueObjects\Sku;
use Cknow\Money\Money;
use Spatie\LaravelData\Data;

class MarketplaceOrderItemData extends Data
{
    public function __construct(
        public MarketplaceProductId $product_id,
        public Quantity $quantity,
        public Money $price,
        public ?Sku $sku = null,
    ) {}
}
