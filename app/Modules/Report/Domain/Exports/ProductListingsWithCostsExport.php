<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Product\Domain\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class ProductListingsWithCostsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(private int $organizationId) {}

    public function query(): Builder
    {
        return ProductListing::query()
            ->forOrganization($this->organizationId)
            ->whereHas('product')
            ->with('product');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Marketplace',
            'SKU',
            'Name',
            'Price ₽',
            'Commission %',
            'Logistic cost ₽',
            'Cost price ₽',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->marketplace->label(),
            $row->vendor_code,
            $row->product->name,
            $row->price->getAmount() / 100,
            $row->commission_percent,
            $row->logistic_cost->getAmount() / 100,
            $row->product->cost_price->getAmount() / 100,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
