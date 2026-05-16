<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class MetricsResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'average_rating' => round($this->resource['average_rating'], 2),
            'total_reviews' => $this->resource['total_reviews'] ?? 0,
            'sentiment_distribution' => [
                'positive' => $this->resource['sentiment_distribution']['positive'] ?? 0,
                'neutral' => $this->resource['sentiment_distribution']['neutral'] ?? 0,
                'negative' => $this->resource['sentiment_distribution']['negative'] ?? 0,
            ],
            'source_distribution' => $this->resource['source_distribution'] ?? [],
            'rating_dynamics' => $this->resource['rating_dynamics'] ?? [],
        ];
    }
}
