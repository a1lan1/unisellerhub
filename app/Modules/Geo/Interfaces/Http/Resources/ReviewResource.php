<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Resources;

use App\Modules\Geo\Domain\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location'),
            'source' => $this->source,
            'author_name' => $this->author_name,
            'text' => $this->text,
            'rating' => $this->rating,
            'sentiment' => $this->sentiment,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
