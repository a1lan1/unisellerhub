<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class MarketplaceConnectionName implements Stringable
{
    public function __construct(private string $value)
    {
        $this->validate($value);
    }

    private function validate(string $value): void
    {
        $trimmedValue = trim($value);
        if ($trimmedValue === '' || $trimmedValue === '0') {
            throw new InvalidArgumentException('Marketplace connection name cannot be empty.');
        }

        if (strlen($trimmedValue) < 3) {
            throw new InvalidArgumentException('Marketplace connection name is too short.');
        }

        if (strlen($trimmedValue) > 255) {
            throw new InvalidArgumentException('Marketplace connection name is too long.');
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
