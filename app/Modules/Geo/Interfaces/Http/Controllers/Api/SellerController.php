<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Geo\Domain\Interfaces\SellerServiceInterface;
use App\Modules\Geo\Interfaces\Http\Resources\SellerResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerController extends Controller
{
    public function __construct(private readonly SellerServiceInterface $sellerService) {}

    public function list(): AnonymousResourceCollection
    {
        $sellers = $this->sellerService->getSellers();

        return SellerResource::collection($sellers);
    }
}
