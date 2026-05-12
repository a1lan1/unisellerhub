<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Actions;

use App\Modules\Product\Domain\Models\Product;
use Illuminate\Support\Str;

class GenerateProductEmbeddingAction
{
    public function handle(Product $product): void
    {
        if (config('ai.vector_search.enabled')) {
            // Prepare text for embedding
            $embeddingText = $product->name;
            if ($product->category) {
                $embeddingText .= ' '.$product->category->name;
            }

            if ($product->description) {
                $embeddingText .= ' '.$product->description;
            }

            $product->update([
                'embedding' => Str::of($embeddingText)->toEmbeddings(),
            ]);
        }
    }
}
