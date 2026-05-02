<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Application\Services\ExportService;
use App\Modules\Report\Interfaces\Http\Requests\Export\ExportInventoryRequest;
use App\Modules\Report\Interfaces\Http\Requests\Export\ExportOrdersRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(private readonly ExportService $exportService) {}

    /**
     * Download Orders export file.
     */
    public function orders(ExportOrdersRequest $request): BinaryFileResponse
    {
        return $this->exportService->exportOrders($request->user());
    }

    /**
     * Download Inventory export file.
     */
    public function inventory(ExportInventoryRequest $request): BinaryFileResponse
    {
        return $this->exportService->exportInventory($request->user());
    }
}
