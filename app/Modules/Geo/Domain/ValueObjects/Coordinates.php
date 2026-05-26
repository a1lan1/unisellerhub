<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Coordinates implements Stringable
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {
        $this->validate($latitude, $longitude);
    }

    private function validate(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }
    }

    public function __toString(): string
    {
        return sprintf('%s,%s', $this->latitude, $this->longitude);
    }

    public function equals(self $other): bool
    {
        return $this->latitude === $other->latitude
            && $this->longitude === $other->longitude;
    }

    public function getValue(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
