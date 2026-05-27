<?php

declare(strict_types=1);

namespace App\Modules\Product\Data\Transformers;

use App\Modules\Product\ValueObjects\Sku;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class SkuTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (! $value instanceof Sku) {
            return $value;
        }

        return $value->getValue();
    }
}
