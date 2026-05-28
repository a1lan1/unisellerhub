<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Report\Infrastructure\Jobs\ExportInventoryJob;
use App\Modules\Report\Infrastructure\Jobs\ExportOrdersJob;
use App\Modules\Report\Infrastructure\Jobs\GenerateAnalyticsReportJob;
use App\Modules\Report\Interfaces\Http\Requests\Analytics\GenerateAnalyticsReportRequest;
use App\Modules\Report\Interfaces\Http\Requests\Export\ExportInventoryRequest;
use App\Modules\Report\Interfaces\Http\Requests\Export\ExportOrdersRequest;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    /**
     * Dispatch job to export Orders to Excel.
     */
    public function orders(ExportOrdersRequest $request): JsonResponse
    {
        dispatch(new ExportOrdersJob($request->user(), $request->toDto()));

        return response()->json(['message' => 'Orders export started. You will be notified when the file is ready.']);
    }

    /**
     * Dispatch job to export Inventory to Excel.
     */
    public function inventory(ExportInventoryRequest $request): JsonResponse
    {
        dispatch(new ExportInventoryJob($request->user(), $request->toDto()));

        return response()->json(['message' => 'Inventory export started. You will be notified when the file is ready.']);
    }

    /**
     * Dispatch job to generate analytics report using the Python service.
     */
    public function generateAnalyticsReport(GenerateAnalyticsReportRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        dispatch(new GenerateAnalyticsReportJob(
            $request->user(),
            $dto
        ));

        return response()->json(['message' => 'Analytics report generation started. You will be notified when the file is ready.']);
    }
}
