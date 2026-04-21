<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Resources;

use App\Modules\Product\Domain\Models\ProductListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @property ProductListing $resource */
class ProductListingResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        $product = $this->resource->product;

        return [
            'id' => $this->resource->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'marketplace' => $this->resource->marketplace->value,
            'external_id' => $this->resource->external_id,
            'price' => (int) ($this->resource->price?->getAmount() ?? 0),
            'formatted_price' => $this->resource->price?->format() ?? '0.00 ₽',
            'status' => $this->resource->status,
            'last_synced_at' => $this->resource->last_synced_at?->toDateTimeString(),
        ];
    }
}
