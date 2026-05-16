<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Resources;

use App\Modules\Geo\Domain\Models\ResponseTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin ResponseTemplate
 */
class ResponseTemplateResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->whenHas('created_at'),
        ];
    }
}
