<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Application\Actions\UpdateProductFinanceAction;
use App\Modules\Product\Domain\Data\UpdateProductFinanceData;
use App\Modules\Product\Interfaces\Http\Requests\UpdateProductFinanceRequest;
use App\Modules\Report\Application\Services\AnalyticsService;
use App\Modules\Report\Interfaces\Http\Requests\Analytics\AbcAnalysisRequest;
use App\Modules\Report\Interfaces\Http\Requests\Analytics\ProfitabilityAnalysisRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {}

    public function abc(AbcAnalysisRequest $request): Response
    {
        return Inertia::render('Analytics/Abc', [
            'abc' => $this->analyticsService->getAbcAnalysis($request->user()),
        ]);
    }

    public function profitability(ProfitabilityAnalysisRequest $request): Response
    {
        return Inertia::render('Analytics/Profitability', [
            'items' => $this->analyticsService->getProfitabilityAnalysis($request->user()),
        ]);
    }

    /**
     * Update product cost price or listing fees.
     */
    public function updateFinance(UpdateProductFinanceRequest $request, UpdateProductFinanceAction $action): JsonResponse
    {
        $action->execute(UpdateProductFinanceData::fromRequest($request->validated()));

        return response()->json(['message' => 'Finance data updated successfully!']);
    }
}
