<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Actions;

use App\Modules\User\Domain\Data\CreateOrganizationData;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateOrganizationAction
{
    /**
     * @throws Throwable
     */
    public function execute(CreateOrganizationData $dto): Organization
    {
        return DB::transaction(function () use ($dto) {
            $organization = Organization::create([
                'name' => $dto->name,
                'slug' => Str::slug($dto->name),
            ]);

            /** @var User $user */
            $user = User::find($dto->userId);
            $user->organization_id = $organization->id;
            $user->save();

            return $organization;
        });
    }
}
