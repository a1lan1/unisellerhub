<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Casts;

use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class QuantityCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Quantity
    {
        if (is_null($value)) {
            return null;
        }

        return new Quantity((int) $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Quantity) {
            return $value->getValue();
        }

        return (int) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): int
    {
        return $value instanceof Quantity
            ? $value->getValue()
            : (int) $value;
    }
}
