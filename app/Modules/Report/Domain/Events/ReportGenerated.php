<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReportGenerated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $organizationId,
        public string $filename,
        public string $downloadUrl,
        public string $reportType = 'general'
    ) {}
}
