<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Observers;

use App\Modules\Geo\Domain\Models\ResponseTemplate;
use Illuminate\Support\Facades\Cache;

class ResponseTemplateObserver
{
    public function created(ResponseTemplate $responseTemplate): void
    {
        $this->clearCache();
    }

    public function updated(ResponseTemplate $responseTemplate): void
    {
        $this->clearCache();
    }

    public function deleted(ResponseTemplate $responseTemplate): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['response_templates'])->flush();
    }
}
