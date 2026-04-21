<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Interfaces\Http\Requests\OrderListingsRequest;
use App\Modules\Order\Interfaces\Http\Resources\OrderResource;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(OrderListingsRequest $request): Response
    {
        $filter = $request->toDto();

        $paginator = $this->orderService->getPaginatedOrders($request->user(), $filter);

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($paginator),
            'filters' => $filter->toArray(),
        ]);
    }
}
