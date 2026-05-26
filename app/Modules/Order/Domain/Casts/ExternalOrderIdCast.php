<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Casts;

use App\Modules\Order\Domain\ValueObjects\ExternalOrderId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class ExternalOrderIdCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ExternalOrderId
    {
        if (is_null($value)) {
            return null;
        }

        return new ExternalOrderId((string) $value);
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

        if ($value instanceof ExternalOrderId) {
            return $value->getValue();
        }

        return (string) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof ExternalOrderId
            ? $value->getValue()
            : $value;
    }
}
