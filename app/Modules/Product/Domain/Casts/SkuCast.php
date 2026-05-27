<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Casts;

use App\Modules\Product\ValueObjects\Sku;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class SkuCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Sku
    {
        if (is_null($value)) {
            return null;
        }

        return new Sku((string) $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Sku) {
            return $value->getValue();
        }

        return (string) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof Sku
            ? $value->getValue()
            : $value;
    }
}
