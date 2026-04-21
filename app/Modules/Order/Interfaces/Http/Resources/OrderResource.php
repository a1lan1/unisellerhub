<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Resources;

use App\Modules\Order\Domain\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @property Order $resource */
class OrderResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'external_id' => $this->resource->external_id,
            'marketplace' => $this->resource->marketplace->value,
            'status' => $this->resource->status,
            'total_price' => (float) $this->resource->total_price->getAmount(),
            'formatted_total_price' => $this->resource->total_price->format(),
            'order_date' => $this->resource->order_date->toDateTimeString(),
            'items' => OrderItemResource::collection($this->resource->items),
        ];
    }
}
