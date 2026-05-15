<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Actions;

use App\Modules\Geo\Domain\Data\ReviewData;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreReviewAction
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    /**
     * Store or update a review from external source.
     *
     * @throws Throwable
     */
    public function execute(ReviewData $data): Review
    {
        return DB::transaction(fn (): Review => $this->reviewRepository->updateOrCreate($data));
    }
}
