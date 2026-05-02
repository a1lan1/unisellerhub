<?php

declare(strict_types=1);

namespace App\Modules\Report\Domain\Exports;

use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

readonly class OrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private int $organizationId) {}

    public function collection(): Collection
    {
        return resolve(OrderRepositoryInterface::class)
            ->getForOrdersExport($this->organizationId);
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
            $order->status,
            $order->total_price.' ₽',
            $order->items->count(),
        ];
    }
}
