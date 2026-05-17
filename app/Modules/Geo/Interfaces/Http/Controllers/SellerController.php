<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Domain\Interfaces\SellerServiceInterface;
use App\Modules\Geo\Interfaces\Http\Resources\SellerResource;
use App\Modules\User\Domain\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function __construct(private readonly SellerServiceInterface $sellerService) {}

    public function show(User $seller): Response
    {
        $seller = $this->sellerService->getSellerWithProducts($seller);

        return Inertia::render('geo/Reviews', [
            'seller' => SellerResource::make($seller),
        ]);
    }
}
