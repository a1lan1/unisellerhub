<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Interfaces\ReviewServiceInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

readonly class CachedReviewService implements ReviewServiceInterface
{
    public function __construct(private ReviewServiceInterface $service) {}

    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator
    {
        return Cache::tags(['reviews'])->flexible(
            sprintf(CacheKeyEnum::REVIEWS_USER->value, $user->id, $filters->cacheKey(), $page),
            [Date::now()->addMinutes(15), Date::now()->addHour()],
            fn (): LengthAwarePaginator => $this->service->getReviewsForUser($user, $filters, $page)
        );
    }
}
