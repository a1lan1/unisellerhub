<?php

declare(strict_types=1);

namespace App\Modules\Geo\Infrastructure\Jobs;

use App\Modules\Geo\Application\Actions\StoreReviewAction;
use App\Modules\Geo\Domain\Data\ReviewData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessGeoReview implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ReviewData $reviewData) {}

    public function handle(StoreReviewAction $storeReviewAction): void
    {
        try {
            $storeReviewAction->execute($this->reviewData);
        } catch (Throwable $throwable) {
            Log::error('ProcessGeoReview Job failed: '.$throwable->getMessage(), [
                'data' => $this->reviewData->toArray(),
                'exception' => $throwable,
            ]);

            $this->fail($throwable);
        }
    }
}
