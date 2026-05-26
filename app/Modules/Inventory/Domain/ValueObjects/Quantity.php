<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Quantity implements Stringable
{
    public function __construct(private int $value)
    {
        $this->validate($value);
    }

    private function validate(int $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
