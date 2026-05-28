<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Casts;

use App\Modules\Shared\Domain\ValueObjects\Address;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AddressCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Address
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode((string) $value, true);

        return new Address(
            fullAddress: $data['full_address'] ?? '',
            country: $data['country'] ?? '',
            city: $data['city'] ?? '',
            street: $data['street'] ?? '',
            houseNumber: $data['house_number'] ?? '',
            postalCode: $data['postal_code'] ?? '',
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = new Address(
                fullAddress: $value['fullAddress'] ?? $value['full_address'] ?? '',
                country: $value['country'] ?? '',
                city: $value['city'] ?? '',
                street: $value['street'] ?? '',
                houseNumber: $value['houseNumber'] ?? $value['house_number'] ?? '',
                postalCode: $value['postalCode'] ?? $value['postal_code'] ?? '',
            );
        }

        if (! $value instanceof Address) {
            throw new InvalidArgumentException('The given value is not an Address instance.');
        }

        return json_encode($value->toArray());
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): array
    {
        return $value instanceof Address
            ? $value->toArray()
            : $value;
    }
}
