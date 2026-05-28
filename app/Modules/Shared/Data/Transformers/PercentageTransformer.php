<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data\Transformers;

use App\Modules\Shared\Domain\ValueObjects\Percentage;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class PercentageTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (! $value instanceof Percentage) {
            return $value;
        }

        return $value->getValue();
    }
}
