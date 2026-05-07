<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Product\Domain\Models\ProductListing;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class InventoryExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(
        private int $organizationId,
        private ?ProductListingsFilterData $filters = null
    ) {}

    public function query(): Builder
    {
        $query = ProductListing::query()
            ->forOrganization($this->organizationId)
            ->whereHas('product')
            ->with(['product', 'inventory.warehouse'])
            ->latest();

        if ($this->filters instanceof ProductListingsFilterData) {
            $query->filter($this->filters);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Name',
            'Marketplace',
            'Warehouse',
            'Quantity',
            'Reserved',
        ];
    }

    /**
     * @param  ProductListing  $listing
     */
    public function map($listing): array
    {
        $rows = [];
        foreach ($listing->inventory as $stock) {
            $rows[] = [
                $listing->vendor_code,
                $listing->product->name,
                $listing->marketplace->value,
                $stock->warehouse->name,
                $stock->quantity,
                $stock->reserved,
            ];
        }

        return $rows;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
