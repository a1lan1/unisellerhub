<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Casts;

use App\Modules\Marketplace\Domain\ValueObjects\Credentials;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Override;

class CredentialsCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Credentials
    {
        if (is_null($value)) {
            return null;
        }

        // Laravel's encrypted cast returns a string, so we need to decode it first
        $decodedValue = is_string($value) ? json_decode((string) decrypt($value), true) : $value;

        return new Credentials($decodedValue);
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

        if ($value instanceof Credentials) {
            $value = $value->getValue();
        }

        // Encrypt the array before storing
        return encrypt(
            json_encode($value)
        );
    }

    #[Override]
    public function serialize(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $value instanceof Credentials
            ? $value->getValue()
            : $value;
    }
}
