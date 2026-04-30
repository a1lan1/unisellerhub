<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Application\Actions\PerformGlobalSearchAction;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\SearchRequest;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Perform global search across products and orders using Laravel Scout.
     */
    public function __invoke(SearchRequest $request, PerformGlobalSearchAction $action): JsonResponse
    {
        $query = $request->input('q');
        $organizationId = $request->user()->organization_id;

        $results = $action->execute($query, $organizationId);

        return response()->json([
            'results' => $results->map(fn ($dto): array => $dto->toArray())->all(),
        ]);
    }
}
