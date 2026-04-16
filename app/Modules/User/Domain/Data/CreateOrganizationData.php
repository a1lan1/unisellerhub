<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Data;

class CreateOrganizationData
{
    public function __construct(
        public string $name,
        public int $userId,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            name: (string) $data['name'],
            userId: $userId,
        );
    }
}
