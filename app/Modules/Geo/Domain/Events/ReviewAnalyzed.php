<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Events;

use App\Modules\Geo\Domain\Data\ReviewAnalyzedData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReviewAnalyzed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public ReviewAnalyzedData $data
    ) {}
}
