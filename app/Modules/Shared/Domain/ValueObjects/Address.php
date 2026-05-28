<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Stringable;

final class Address implements Arrayable, Stringable
{
    public function __construct(
        public string $fullAddress,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $street = null,
        public ?string $houseNumber = null,
        public ?string $postalCode = null,
    ) {
        $this->validate($fullAddress);
    }

    private function validate(string $fullAddress): void
    {
        if ($fullAddress === '' || $fullAddress === '0') {
            throw new InvalidArgumentException('Full address cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->fullAddress;
    }

    public function equals(self $other): bool
    {
        return $this->country === $other->country
            && $this->city === $other->city
            && $this->street === $other->street
            && $this->houseNumber === $other->houseNumber
            && $this->postalCode === $other->postalCode
            && $this->fullAddress === $other->fullAddress;
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
