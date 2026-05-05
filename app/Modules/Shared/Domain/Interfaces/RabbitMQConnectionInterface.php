<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Interfaces;

use PhpAmqpLib\Connection\AMQPStreamConnection;

interface RabbitMQConnectionInterface
{
    public function connect(): ?AMQPStreamConnection;
}
