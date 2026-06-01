<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Domain\Interfaces;

interface PriceAnalysisSyncResultProcessorInterface
{
    /**
     * Process a successful price analysis result.
     */
    public function processSuccess(array $data): void;

    /**
     * Process a failed price analysis result.
     */
    public function processFailure(array $data): void;
}
