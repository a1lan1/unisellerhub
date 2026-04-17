<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SyncFailed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $organizationId,
        public string $type, // 'products', 'orders', 'inventory'
        public string $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.'.$this->organizationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sync.failed';
    }
}
