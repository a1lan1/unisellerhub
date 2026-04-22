<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Interfaces\Http\Resources;

use App\Modules\Inventory\Domain\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @property Inventory $resource */
class InventoryResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        $listing = $this->resource->listing;
        $product = $listing->product;
        $warehouse = $this->resource->warehouse;

        return [
            'id' => $this->resource->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'marketplace' => $listing->marketplace->value,
            'warehouse_name' => $warehouse->name,
            'quantity' => (int) $this->resource->quantity,
            'reserved' => (int) $this->resource->reserved,
            'listing_id' => $this->resource->product_listing_id,
            'updated_at' => $this->resource->updated_at?->toDateTimeString(),
        ];
    }
}
