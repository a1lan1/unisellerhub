<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Application\Actions\StoreReviewAction;
use App\Modules\Geo\Domain\Data\ReviewFilterData;
use App\Modules\Geo\Domain\Interfaces\GeoMetricServiceInterface;
use App\Modules\Geo\Domain\Interfaces\ReviewServiceInterface;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Interfaces\Http\Requests\StoreReviewRequest;
use App\Modules\Geo\Interfaces\Http\Resources\MetricsResource;
use App\Modules\Geo\Interfaces\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class ReviewController extends Controller
{
    public function __construct(
        private readonly GeoMetricServiceInterface $geoMetricService,
        private readonly ReviewServiceInterface $reviewService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = ReviewFilterData::from($request->all());

        $reviews = $this->reviewService->getReviewsForUser(
            $request->user(),
            $filters,
            $request->integer('page', 1)
        );

        return ReviewResource::collection($reviews);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreReviewRequest $request, StoreReviewAction $storeReviewAction): ReviewResource
    {
        $this->authorize('create', Review::class);

        $review = $storeReviewAction->execute($request->toDto());

        return ReviewResource::make($review);
    }

    public function metrics(Request $request): MetricsResource
    {
        $metrics = $this->geoMetricService->calculateForUser(
            $request->user(),
            $request->has('location_id') ? $request->integer('location_id') : null
        );

        return new MetricsResource($metrics);
    }
}
