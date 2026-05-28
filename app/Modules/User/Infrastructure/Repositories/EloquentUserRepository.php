<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Repositories;

use App\Modules\User\Domain\Enums\RoleEnum;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User
    {
        return User::query()
            ->select(['id', 'name'])
            ->findOrFail($id);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * @return Collection<int, User>
     */
    public function searchByNameOrEmail(string $query, int $limit = 20, ?int $excludeUserId = null): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->with('media')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->when($query, function ($q) use ($query): void {
                $q->where(function ($sub) use ($query): void {
                    $sub->where('email', 'like', sprintf('%%%s%%', $query))
                        ->orWhere('name', 'like', sprintf('%%%s%%', $query));
                });
            })
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function getSellers(int $limit = 20): Collection
    {
        return User::query()
            ->role(RoleEnum::SELLER)
            ->with('media')
            ->limit($limit)
            ->get();
    }

    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User
    {
        return $seller->load(['organization.products' => fn (HasMany $query) => $query->latest()->take($productsLimit)]);
    }
}
