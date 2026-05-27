<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class ExternalProductId implements Stringable
{
    public function __construct(private string $value)
    {
        $this->validate($value);
    }

    private function validate(string $value): void
    {
        if (in_array(trim($value), ['', '0'], true)) {
            throw new InvalidArgumentException('External Product ID cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
