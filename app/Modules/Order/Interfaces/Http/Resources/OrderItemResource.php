<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Resources;

use App\Modules\Order\Domain\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @property OrderItem $resource */
class OrderItemResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        $listing = $this->resource->listing;
        $product = $listing?->product;

        return [
            'product_name' => $product ? $product->name : 'Unknown Product',
            'sku' => $product ? $product->sku : 'Unknown SKU',
            'quantity' => (int) $this->resource->quantity,
            'price' => (int) $this->resource->price->getAmount(),
            'formatted_price' => $this->resource->price->format(),
        ];
    }
}
