<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Application\Services\ProductService;
use App\Modules\Product\Interfaces\Http\Requests\ProductListingsRequest;
use App\Modules\Product\Interfaces\Http\Resources\ProductListingResource;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(ProductListingsRequest $request): Response
    {
        $filter = $request->toDto();

        $paginator = $this->productService->getPaginatedListings($request->user(), $filter);

        return Inertia::render('Products/Index', [
            'products' => ProductListingResource::collection($paginator),
            'filters' => $filter->toArray(),
        ]);
    }
}
