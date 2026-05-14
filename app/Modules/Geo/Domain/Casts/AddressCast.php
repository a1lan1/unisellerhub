<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Casts;

use App\Modules\Shared\ValueObjects\Address;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AddressCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Address
    {
        if (! isset($attributes['address'])) {
            return null;
        }

        $data = json_decode((string) $attributes['address'], true);

        return new Address(
            country: $data['country'] ?? '',
            city: $data['city'] ?? '',
            street: $data['street'] ?? '',
            houseNumber: $data['house_number'] ?? '',
            postalCode: $data['postal_code'] ?? '',
            fullAddress: $data['full_address'] ?? '',
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = new Address(
                country: $value['country'] ?? '',
                city: $value['city'] ?? '',
                street: $value['street'] ?? '',
                houseNumber: $value['houseNumber'] ?? $value['house_number'] ?? '',
                postalCode: $value['postalCode'] ?? $value['postal_code'] ?? '',
                fullAddress: $value['fullAddress'] ?? $value['full_address'] ?? '',
            );
        }

        if (! $value instanceof Address) {
            throw new InvalidArgumentException('The given value is not an Address instance.');
        }

        return json_encode($value->toArray());
    }
}
