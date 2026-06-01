<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Interfaces\SellerServiceInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class SellerService implements SellerServiceInterface
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    /**
     * @return Collection<User>
     */
    public function getSellers(): Collection
    {
        return Cache::tags(['sellers'])->remember(
            CacheKeyEnum::SELLERS->value,
            Date::now()->addHour(),
            fn (): Collection => $this->userRepository->getSellers()
        );
    }

    public function getSellerWithProducts(User $seller): User
    {
        return Cache::tags(['sellers'])->remember(
            sprintf(CacheKeyEnum::SELLERS_WITH_PRODUCTS->value, $seller->id),
            Date::now()->addHour(),
            fn (): User => $this->userRepository->getSellerWithProducts($seller)
        );
    }
}
