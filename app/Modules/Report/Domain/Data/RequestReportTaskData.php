<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Data;

use Override;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RequestReportTaskData extends Data
{
    public function __construct(
        public int $organization_id,
        public string $report_type,
        public array $data,
        public ?string $batch_id = null,
        public ?string $id = null,
        public ?string $display_name = null,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'organization_id' => $this->organization_id,
            'report_type' => $this->report_type,
            'data' => $this->data,
            'batch_id' => $this->batch_id,
            'id' => $this->id,
            'displayName' => $this->display_name,
        ];
    }

    public function toJsonEncode(): string
    {
        return json_encode(
            $this->toArray()
        );
    }
}
