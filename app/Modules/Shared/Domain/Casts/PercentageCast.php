<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Casts;

use App\Modules\Shared\Domain\ValueObjects\Percentage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class PercentageCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Percentage
    {
        if (is_null($value)) {
            return null;
        }

        return new Percentage((float) $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function set(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Percentage) {
            return $value->getValue();
        }

        return (float) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): float
    {
        return $value instanceof Percentage
            ? $value->getValue()
            : (float) $value;
    }
}
