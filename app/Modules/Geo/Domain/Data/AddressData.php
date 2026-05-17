<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AddressData extends Data
{
    public function __construct(
        public ?string $country,
        public ?string $city,
        public ?string $street,
        public ?string $houseNumber,
        public ?string $postalCode,
        public string $fullAddress,
    ) {}
}
