<?php

declare(strict_types=1);

namespace App\Modules\Activity\Domain\Interfaces;

interface ActivityLoggerInterface
{
    /**
     * Log a business activity for an organization.
     */
    public function log(
        int $organizationId,
        string $message,
        string $type = 'info',
        ?array $properties = []
    ): void;
}
