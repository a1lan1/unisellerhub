<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

final readonly class Address implements Arrayable
{
    public function __construct(
        public string $country,
        public string $city,
        public string $street,
        public string $houseNumber,
        public string $postalCode,
        public string $fullAddress,
    ) {
        if ($fullAddress === '' || $fullAddress === '0') {
            throw new InvalidArgumentException('Full address cannot be empty.');
        }
    }

    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'city' => $this->city,
            'street' => $this->street,
            'house_number' => $this->houseNumber,
            'postal_code' => $this->postalCode,
            'full_address' => $this->fullAddress,
        ];
    }
}
