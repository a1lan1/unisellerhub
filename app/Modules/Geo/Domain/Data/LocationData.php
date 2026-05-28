<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Data;

use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\ValueObjects\Coordinates;
use App\Modules\Shared\Domain\ValueObjects\Address;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class LocationData extends Data
{
    /**
     * @param  array<string, mixed>|null  $externalIds
     */
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public LocationTypeEnum $type,
        public Address $address,
        public Coordinates $coordinates,
        public ?array $externalIds = null,
    ) {}
}
