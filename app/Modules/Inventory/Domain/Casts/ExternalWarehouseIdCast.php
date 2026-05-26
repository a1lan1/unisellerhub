<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Casts;

use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class ExternalWarehouseIdCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ExternalWarehouseId
    {
        if (is_null($value)) {
            return null;
        }

        return new ExternalWarehouseId((string) $value);
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

        if ($value instanceof ExternalWarehouseId) {
            return $value->getValue();
        }

        return (string) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value instanceof ExternalWarehouseId
            ? $value->getValue()
            : $value;
    }
}
