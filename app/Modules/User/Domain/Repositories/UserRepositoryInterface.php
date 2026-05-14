<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Repositories;

use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getSellerWithProducts(User $seller, int $productsLimit = 8): User;

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User;

    /**
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email): User;

    /**
     * @return Collection<int, User>
     */
    public function searchByNameOrEmail(string $query, int $limit = 20, ?int $excludeUserId = null): Collection;

    /**
     * @return Collection<int, User>
     */
    public function getSellers(int $limit = 20): Collection;
}
