<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Repositories;

use App\Modules\Geo\Domain\Data\ReviewData;
use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    public function getForUserWithFilters(User $user, ReviewFilterData $filters, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * @return Collection<int, Review>
     */
    public function getForUserAndLocation(User $user, ?int $locationId = null): Collection;

    public function updateOrCreate(ReviewData $data): Review;
}
