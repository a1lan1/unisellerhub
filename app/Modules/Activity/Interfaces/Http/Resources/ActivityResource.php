<?php

declare(strict_types=1);

namespace App\Modules\Activity\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property Activity $resource
 */
class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'description' => $this->resource->description,
            'properties' => $this->resource->properties,
            'created_at' => $this->resource->created_at->toDateTimeString(),
            'human_time' => $this->resource->created_at->diffForHumans(),
        ];
    }
}
