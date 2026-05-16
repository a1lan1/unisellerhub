<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Collection;

interface SellerServiceInterface
{
    public function getSellerWithProducts(User $seller): User;

    public function getSellers(): Collection;
}
