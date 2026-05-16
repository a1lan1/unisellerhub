<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewServiceInterface
{
    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator;
}
