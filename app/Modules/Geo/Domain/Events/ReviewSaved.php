<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Events;

use App\Modules\Geo\Domain\Models\Review;
use App\Modules\Geo\Interfaces\Http\Resources\ReviewResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;

class ReviewSaved implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Review $review)
    {
        Log::info('ReviewSaved: Recipient is not a User model.', ['Channel' => sprintf('reviews.location_id.%s', $this->review->location_id)]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel(sprintf('reviews.location_id.%s', $this->review->location_id)),
        ];
    }

    public function broadcastWith(): array
    {
        $this->review->loadMissing('location');

        return [
            'review' => new ReviewResource($this->review),
        ];
    }

    public function broadcastAs(): string
    {
        return 'review.saved';
    }
}
