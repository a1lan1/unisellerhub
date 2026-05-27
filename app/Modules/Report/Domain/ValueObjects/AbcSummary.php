<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\ValueObjects;

use App\Modules\Report\Domain\Enums\AbcGroupEnum;
use InvalidArgumentException;
use Stringable;

final readonly class AbcSummary implements Stringable
{
    /**
     * @param  array<AbcGroupEnum, int>  $counts
     */
    public function __construct(private array $counts)
    {
        $this->validate($counts);
    }

    private function validate(array $counts): void
    {
        foreach (AbcGroupEnum::cases() as $group) {
            if (! isset($counts[$group->value]) || ! is_int($counts[$group->value]) || $counts[$group->value] < 0) {
                throw new InvalidArgumentException(sprintf('Invalid count for ABC group %s.', $group->value));
            }
        }
    }

    public function __toString(): string
    {
        return sprintf('A: %d, B: %d, C: %d', $this->counts[AbcGroupEnum::A->value], $this->counts[AbcGroupEnum::B->value], $this->counts[AbcGroupEnum::C->value]);
    }

    public function equals(self $other): bool
    {
        return $this->counts === $other->counts;
    }

    /**
     * @return array<AbcGroupEnum, int>
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    public function getCountForGroup(AbcGroupEnum $group): int
    {
        return $this->counts[$group->value];
    }
}
