<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class CleanupOldReportsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reports:cleanup-old';

    /**
     * @var string
     */
    protected $description = 'Clean up old generated reports from storage.';

    public function handle(): void
    {
        $this->info('Starting cleanup of old reports...');

        $deletedCount = 0;

        $disk = Storage::disk('reports');
        $files = $disk->files();

        foreach ($files as $file) {
            // Get the last modified timestamp of the file
            $lastModified = $disk->lastModified($file);

            // Define the age limit for reports
            $ageLimitInSeconds = 60 * 60 * 24; // 24 hours

            if ((time() - $lastModified) > $ageLimitInSeconds) {
                $disk->delete($file);
                $deletedCount++;
                Log::info('Deleted old report file.', ['file' => $file]);
            }
        }

        $this->info(sprintf('Finished cleanup. Deleted %d old report(s).', $deletedCount));
        Log::info(sprintf('Finished cleanup of old reports. Deleted %d old report(s).', $deletedCount));
    }
}
