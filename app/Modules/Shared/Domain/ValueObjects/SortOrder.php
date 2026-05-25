<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class SortOrder implements Stringable
{
    public const string DIRECTION_ASC = 'asc';

    public const string DIRECTION_DESC = 'desc';

    public function __construct(
        public string $field,
        public string $direction = self::DIRECTION_ASC
    ) {
        $this->validate($field, $direction);
    }

    private function validate(string $field, string $direction): void
    {
        if (in_array(trim($field), ['', '0'], true)) {
            throw new InvalidArgumentException('Sort field cannot be empty.');
        }

        if (! in_array($direction, [self::DIRECTION_ASC, self::DIRECTION_DESC], true)) {
            throw new InvalidArgumentException('Invalid sort direction. Must be "asc" or "desc".');
        }
    }

    public function __toString(): string
    {
        return sprintf('%s:%s', $this->field, $this->direction);
    }

    public function equals(self $other): bool
    {
        return $this->field === $other->field
            && $this->direction === $other->direction;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
