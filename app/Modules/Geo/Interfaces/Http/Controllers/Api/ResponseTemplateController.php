<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Domain\Interfaces\ResponseTemplateServiceInterface;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Interfaces\Http\Requests\ResponseTemplateRequest;
use App\Modules\Geo\Interfaces\Http\Resources\ResponseTemplateResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResponseTemplateController extends Controller
{
    public function __construct(private readonly ResponseTemplateServiceInterface $responseTemplateService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ResponseTemplate::class);

        $templates = $this->responseTemplateService->getTemplatesForUser($request->user());

        return ResponseTemplateResource::collection($templates);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(ResponseTemplateRequest $request): ResponseTemplateResource
    {
        $this->authorize('create', ResponseTemplate::class);

        $template = $this->responseTemplateService->storeTemplate($request->toDto());

        return new ResponseTemplateResource($template);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(ResponseTemplate $responseTemplate): ResponseTemplateResource
    {
        $this->authorize('view', $responseTemplate);

        return new ResponseTemplateResource($responseTemplate);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ResponseTemplateRequest $request, ResponseTemplate $responseTemplate): ResponseTemplateResource
    {
        $this->authorize('update', $responseTemplate);

        $template = $this->responseTemplateService->storeTemplate($request->toDto());

        return new ResponseTemplateResource($template);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(ResponseTemplate $responseTemplate): JsonResponse
    {
        $this->authorize('delete', $responseTemplate);

        $this->responseTemplateService->deleteTemplate($responseTemplate);

        return response()->json(null, 204);
    }
}
