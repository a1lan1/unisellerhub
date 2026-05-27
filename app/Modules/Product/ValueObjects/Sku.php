<?php

declare(strict_types=1);

namespace App\Modules\Product\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Sku implements Stringable
{
    public function __construct(private string $value)
    {
        $this->validate($value);
    }

    private function validate(string $value): void
    {
        if (in_array(trim($value), ['', '0'], true)) {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }

        if (strlen($value) < 3) {
            throw new InvalidArgumentException('SKU is too short.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Sku $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
