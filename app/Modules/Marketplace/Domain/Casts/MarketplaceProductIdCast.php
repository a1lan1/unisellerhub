<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Casts;

use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceProductId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class MarketplaceProductIdCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?MarketplaceProductId
    {
        if (is_null($value)) {
            return null;
        }

        return new MarketplaceProductId((string) $value);
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

        if ($value instanceof MarketplaceProductId) {
            return $value->getValue();
        }

        return (string) $value;
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof MarketplaceProductId
            ? $value->getValue()
            : $value;
    }
}
