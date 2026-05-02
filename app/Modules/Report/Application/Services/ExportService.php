<?php

declare(strict_types=1);

namespace App\Modules\Report\Application\Services;

use App\Modules\Report\Domain\Exports\InventoryExport;
use App\Modules\Report\Domain\Exports\OrdersExport;
use App\Modules\User\Domain\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

readonly class ExportService
{
    /**
     * Export Orders to Excel.
     */
    public function exportOrders(User $user): BinaryFileResponse
    {
        $fileName = 'orders_export_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new OrdersExport($user->organization_id), $fileName);
    }

    /**
     * Export Inventory to Excel.
     */
    public function exportInventory(User $user): BinaryFileResponse
    {
        $fileName = 'inventory_export_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new InventoryExport($user->organization_id), $fileName);
    }
}
