<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Scopes;

use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\Shared\Exceptions\TenantNotSetException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserOrganizationScope implements Scope
{
    /**
     * @throws TenantNotSetException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $organizationId = resolve(TenantManager::class)->getOrganizationId();

        // If the organization ID is not set in the TenantManager, throw an exception.
        // This ensures that we never execute a query without filtering by organization.
        // The context must be set to Middleware or Job.
        if ($organizationId === null) {
            throw new TenantNotSetException;
        }

        $builder->where($model->getTable().'.organization_id', $organizationId);
    }
}
