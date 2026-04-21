<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrdersSynced implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public int $organizationId) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organization.'.$this->organizationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'orders.synced';
    }
}
