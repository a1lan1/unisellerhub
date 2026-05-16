<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Events;

use App\Modules\Geo\Domain\Models\Review;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NegativeSentimentDetected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Review $review) {}
}
