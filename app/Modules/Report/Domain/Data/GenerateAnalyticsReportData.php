<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use Spatie\LaravelData\Data;

class GenerateAnalyticsReportData extends Data
{
    public function __construct(
        public ReportTypeEnum $reportType,
        public int $days,
        public string $endDate,
    ) {}
}
