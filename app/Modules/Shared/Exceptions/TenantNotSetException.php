<?php

declare(strict_types=1);

namespace App\Modules\Shared\Exceptions;

use RuntimeException;

class TenantNotSetException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tenant context (organization_id) is not set for the current request or job.');
    }
}
