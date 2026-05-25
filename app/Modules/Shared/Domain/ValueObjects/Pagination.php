<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Pagination implements Stringable
{
    public function __construct(
        public int $perPage,
        public int $page
    ) {
        $this->validate($perPage, $page);
    }

    private function validate(int $perPage, int $page): void
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('Per page value must be at least 1.');
        }

        if ($page < 1) {
            throw new InvalidArgumentException('Page number must be at least 1.');
        }
    }

    public function __toString(): string
    {
        return sprintf('Page %d, %d items per page', $this->page, $this->perPage);
    }

    public function equals(self $other): bool
    {
        return $this->perPage === $other->perPage
            && $this->page === $other->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
