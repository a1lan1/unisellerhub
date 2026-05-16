<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Interfaces\ReviewServiceInterface;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService implements ReviewServiceInterface
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator
    {
        return $this->reviewRepository->getForUserWithFilters($user, $filters, 15, $page);
    }
}
