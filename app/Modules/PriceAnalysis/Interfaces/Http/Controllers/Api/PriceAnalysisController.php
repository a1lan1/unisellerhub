<?php

declare(strict_types=1);

namespace App\Modules\PriceAnalysis\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\PriceAnalysis\Infrastructure\Jobs\InitiatePriceAnalysisReportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PriceAnalysisController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $organizationId = (int) $request->user()->organization_id;
        $userId = (int) $request->user()->id;

        // Dispatch the job to initiate the price analysis report generation for the entire organization
        dispatch(new InitiatePriceAnalysisReportJob($organizationId, $userId));

        return response()->json(['message' => 'Price analysis report generation job dispatched!']);
    }
}
