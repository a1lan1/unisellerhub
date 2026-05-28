<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Data;

use App\Modules\User\Domain\ValueObjects\OrganizationName;

class CreateOrganizationData
{
    public function __construct(
        public OrganizationName $name,
        public int $userId,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            name: new OrganizationName((string) $data['name']),
            userId: $userId,
        );
    }
}
