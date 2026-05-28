<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Exceptions;

use Exception;
use Throwable;

class InvalidMarketplaceDataException extends Exception
{
    public function __construct(
        string $message = 'Invalid marketplace data received.',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly array $rawData = [],
        public readonly ?string $marketplace = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
