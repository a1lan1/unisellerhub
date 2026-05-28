<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use App\Modules\Report\Domain\ValueObjects\BatchId;
use App\Modules\Report\Domain\ValueObjects\ReportDisplayName;
use Override;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RequestReportTaskData extends Data
{
    public function __construct(
        public int $organization_id,
        public ReportTypeEnum $report_type,
        public array $data,
        public ?BatchId $batch_id = null,
        public ?string $id = null,
        public ?ReportDisplayName $display_name = null,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'organization_id' => $this->organization_id,
            'report_type' => $this->report_type->value,
            'data' => $this->data,
            'batch_id' => $this->batch_id?->getValue(),
            'id' => $this->id,
            'displayName' => $this->display_name?->getValue(),
        ];
    }

    public function toJsonEncode(): string
    {
        return json_encode(
            $this->toArray()
        );
    }
}
