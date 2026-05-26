<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Credentials implements Stringable
{
    /**
     * @param  array<string, mixed>  $value
     */
    public function __construct(private array $value)
    {
        $this->validate($value);
    }

    private function validate(array $value): void
    {
        if ($value === []) {
            throw new InvalidArgumentException('Credentials cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return (string) json_encode($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValue(): array
    {
        return $this->value;
    }
}
