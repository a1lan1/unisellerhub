<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class OrdersExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(
        private int $organizationId,
        private ?OrderFilterData $filters = null
    ) {}

    public function query(): Builder
    {
        $query = Order::query()
            ->forOrganization($this->organizationId)
            ->with('items')
            ->latest();

        if ($this->filters instanceof OrderFilterData) {
            $query->filter($this->filters);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Marketplace',
            'Date',
            'Status',
            'Total Price',
            'Items Count',
        ];
    }

    /**
     * @param  Order  $order
     */
    public function map($order): array
    {
        return [
            $order->external_id,
            $order->marketplace->label(),
            $order->order_date->toDateTimeString(),
            $order->status->value,
            $order->total_price.' ₽',
            $order->items->count(),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
