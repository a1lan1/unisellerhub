<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Interfaces;

use Throwable;

interface SyncResultProcessorInterface
{
    /**
     * Process a successful synchronization result.
     *
     * @param  array  $data  The data received from the RabbitMQ message.
     *
     * @throws Throwable
     */
    public function processSuccess(array $data): void;

    /**
     * Handle a failed synchronization result.
     *
     * @param  array  $data  The data received from the RabbitMQ message.
     */
    public function processFailure(array $data): void;
}
