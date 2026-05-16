<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Observers;

use App\Modules\Geo\Domain\Models\Location;
use Illuminate\Support\Facades\Cache;

class LocationObserver
{
    public function created(Location $location): void
    {
        $this->clearCache();
    }

    public function updated(Location $location): void
    {
        $this->clearCache();
    }

    public function deleted(Location $location): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['locations'])->flush();
    }
}
