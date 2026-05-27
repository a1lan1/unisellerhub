<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Domain\ValueObjects;

use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use Stringable;

final readonly class SalesHistoryItem implements Stringable
{
    public function __construct(
        public DateTimeImmutable $date,
        public Quantity $quantity
    ) {}

    public function __toString(): string
    {
        return sprintf('%s: %d units', $this->date->format('Y-m-d'), $this->quantity->getValue());
    }

    public function equals(self $other): bool
    {
        return $this->date == $other->date
            && $this->quantity->equals($other->quantity);
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'quantity' => $this->quantity->getValue(),
        ];
    }
}
