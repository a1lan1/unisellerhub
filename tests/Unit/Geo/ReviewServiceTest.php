<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\ReviewService;
use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Repositories\ReviewRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

beforeEach(function (): void {
    $this->reviewRepository = $this->mock(ReviewRepositoryInterface::class);
    $this->reviewService = new ReviewService($this->reviewRepository);
});

it('gets reviews for a user with filters', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $filters = ReviewFilterData::from(['rating' => 5]);
    $page = 1;
    $paginator = $this->mock(LengthAwarePaginator::class);

    $this->reviewRepository->shouldReceive('getForUserWithFilters')
        ->once()
        ->with($user, $filters, 15, $page)
        ->andReturn($paginator);

    $result = $this->reviewService->getReviewsForUser($user, $filters, $page);

    expect($result)->toEqual($paginator);
});
