<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @property DatabaseNotification $resource
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->data['title'] ?? '',
            'message' => $this->resource->data['message'] ?? '',
            'type' => $this->resource->data['type'] ?? 'info',
            'action_url' => $this->resource->data['action_url'] ?? null,
            'icon' => $this->resource->data['icon'] ?? null,
            'read_at' => $this->resource->read_at?->toDateTimeString(),
            'created_at' => $this->resource->created_at?->toDateTimeString(),
        ];
    }
}
