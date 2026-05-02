<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class InventoryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private int $organizationId) {}

    public function collection(): Collection
    {
        return resolve(ProductListingRepositoryInterface::class)
            ->getForInventoryExport($this->organizationId);
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
}
