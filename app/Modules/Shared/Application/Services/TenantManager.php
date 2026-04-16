<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Services;

class TenantManager
{
    private ?int $organizationId = null;

    public function setOrganizationId(int $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organizationId;
    }

    public function clear(): void
    {
        $this->organizationId = null;
    }
}
