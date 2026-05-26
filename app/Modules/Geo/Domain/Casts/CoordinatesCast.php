<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Casts;

use App\Modules\Geo\Domain\ValueObjects\Coordinates;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CoordinatesCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Coordinates
    {
        if (! isset($attributes['latitude']) || ! isset($attributes['longitude'])) {
            return null;
        }

        return new Coordinates(
            latitude: (float) $attributes['latitude'],
            longitude: (float) $attributes['longitude']
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (! $value instanceof Coordinates) {
            throw new InvalidArgumentException('The given value is not a Coordinates instance.');
        }

        return $value->getValue();
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $value instanceof Coordinates
            ? $value->getValue()
            : $value;
    }
}
