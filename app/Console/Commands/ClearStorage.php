<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearStorage extends Command
{
    /**
     * @var string
     */
    protected $signature = 'storage:clear';

    /**
     * @var string
     */
    protected $description = 'Clear specified storage directories';

    public function handle(): void
    {
        $directories = [
            storage_path('logs'),
            storage_path('rector'),
            storage_path('debugbar'),
            storage_path('app/public'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $directory) {
            $this->clearDirectory($directory);
            $this->info('Storage directory cleared: '.$directory);
        }

        $this->info('Storage directories cleared successfully.');
    }

    protected function clearDirectory(string $directory): void
    {
        if (File::exists($directory)) {
            $files = File::allFiles($directory);
            $directories = File::directories($directory);

            foreach ($files as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file->getPathname());
                }
            }

            foreach ($directories as $subDirectory) {
                if (basename($subDirectory) !== '.gitignore') {
                    File::deleteDirectory($subDirectory);
                }
            }
        }
    }
}
