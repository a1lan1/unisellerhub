<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use Stringable;

final readonly class Percentage implements Stringable
{
    public function __construct(private float $value) {}

    public function __toString(): string
    {
        return $this->value.'%';
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
