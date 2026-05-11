<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Infrastructure\Jobs;

use App\Modules\User\Application\Services\NotificationService;
use App\Modules\User\Domain\Data\NotificationData;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

#[Backoff(5)]
#[Tries(3)]
final class NotifyUserOfReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly string $batchId) {}

    public function handle(NotificationService $notificationService): void
    {
        $batchKey = 'price_analysis:batch:'.$this->batchId;
        $batchMetadata = json_decode(Redis::get($batchKey) ?: '{}', true);

        if (empty($batchMetadata) || ! isset($batchMetadata['user_id']) || ! isset($batchMetadata['download_url']) || ! isset($batchMetadata['report_filename'])) {
            Log::error('Cannot notify user: batch metadata incomplete or not found in Redis.', ['batch_id' => $this->batchId, 'metadata' => $batchMetadata]);
            if (! empty($batchMetadata)) {
                $batchMetadata['status'] = 'notification_failed';
                Redis::set($batchKey, json_encode($batchMetadata));
            }

            return;
        }

        $user = User::find($batchMetadata['user_id']);

        if (! $user) {
            Log::error('Cannot notify user: user not found.', ['user_id' => $batchMetadata['user_id']]);

            return;
        }

        $downloadUrl = $batchMetadata['download_url']; // This is already the signed URL

        // Create NotificationData object
        $notificationData = new NotificationData(
            title: 'Price Analysis Report Ready',
            message: 'Your price analysis report is ready for download.',
            type: NotificationTypeEnum::SUCCESS,
            actionUrl: $downloadUrl,
            icon: 'mdi-microsoft-excel'
        );

        $notificationService->sendToUser($user, $notificationData);

        Log::info('User notified about price analysis report.', ['batch_id' => $this->batchId, 'user_id' => $user->id]);

        // Clean up batch metadata from Redis after completion
        Redis::del($batchKey);
    }
}
