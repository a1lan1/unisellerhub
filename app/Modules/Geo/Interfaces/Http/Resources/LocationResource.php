<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Resources;

use App\Modules\Geo\Domain\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Location
 */
class LocationResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'external_ids' => $this->whenHas('external_ids'),
            'reviews_count' => $this->whenCounted('reviews'),
            'reviews_avg_rating' => $this->whenAggregated('reviews', 'rating', 'avg'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
        ];
    }
}
