<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Observers;

use App\Modules\Product\Application\Actions\GenerateProductEmbeddingAction;
use App\Modules\Product\Domain\Models\Product;
use Exception;

class ProductObserver
{
    /**
     * @throws Exception
     */
    public function saved(Product $product): void
    {
        // If the product was just created OR fields affecting the embedding have changed
        if ($product->wasRecentlyCreated || $product->isDirty(['name', 'category_id', 'description'])) {
            resolve(GenerateProductEmbeddingAction::class)->handle($product);
        }
    }
}
