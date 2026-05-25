<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;
use Stringable;

final readonly class DateRange implements Stringable
{
    public function __construct(
        public DateTimeImmutable $from,
        public DateTimeImmutable $to
    ) {
        $this->validate($from, $to);
    }

    private function validate(DateTimeImmutable $from, DateTimeImmutable $to): void
    {
        if ($from > $to) {
            throw new InvalidArgumentException('Start date cannot be after end date.');
        }
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->from->format('Y-m-d'), $this->to->format('Y-m-d'));
    }

    public function equals(self $other): bool
    {
        return $this->from == $other->from
            && $this->to == $other->to;
    }
}
