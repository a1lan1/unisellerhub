<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Order\Domain\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class ProductRevenueExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(
        private int $organizationId,
        private string $endDate,
        private int $days = 30,
    ) {}

    public function query(): Builder
    {
        return OrderItem::query()
            ->revenue($this->organizationId, $this->endDate, $this->days);
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Product',
            'Revenue ₽',
        ];
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->product_name,
            $row->revenue / 100,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
