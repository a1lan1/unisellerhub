<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\Actions\CreateOrganizationAction;
use App\Modules\User\Domain\Data\CreateOrganizationData;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Interfaces\Http\Requests\Api\CreateOrganizationRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class OrganizationController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(CreateOrganizationRequest $request, CreateOrganizationAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $organization = $action->execute(CreateOrganizationData::fromRequest(
            $request->validated(),
            $user->id
        ));

        return response()->json([
            'message' => 'Organization created successfully!',
            'organization_id' => $organization->id,
        ], 201);
    }
}
