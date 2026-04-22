<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Actions;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Order\Application\Services\OrderService;
use App\Modules\Order\Domain\Data\OrderData;
use App\Modules\Order\Domain\Events\OrdersSynced;
use App\Modules\Order\Domain\Models\OrderItem;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class SaveMarketplaceOrdersAction
{
    public function __construct(
        private OrderService $orderService,
        private ProductListingRepositoryInterface $productListingRepository
    ) {}

    /**
     * @param  iterable<OrderData>  $orders
     *
     * @throws Throwable
     */
    public function execute(MarketplaceConnection $connection, iterable $orders): void
    {
        $ordersCollection = collect($orders);

        $ordersCollection->chunk(50)->each(function (Collection $chunkOrders) use ($connection): void {
            DB::transaction(function () use ($connection, $chunkOrders): void {
                foreach ($chunkOrders as $orderData) {
                    $this->saveOrder($connection, $orderData);
                }
            });
        });

        event(new OrdersSynced($connection->organization_id));
    }

    private function saveOrder(MarketplaceConnection $connection, OrderData $orderData): void
    {
        // 1. Find or create Order
        $order = $this->orderService->updateOrCreateOrder(
            $connection->marketplace,
            $orderData,
            $connection->organization_id
        );

        // 2. Sync Order Items
        $order->items()->delete();

        foreach ($orderData->items as $itemData) {
            // Try to find listing by external_id first, then by vendor_code
            $listing = $this->productListingRepository->findListingByExternalId($connection->marketplace, $itemData->product_id);

            if (! $listing && ! empty($itemData->sku)) {
                $listing = $this->productListingRepository->findListingByVendorCode($connection->marketplace, $itemData->sku);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_listing_id' => $listing?->id,
                'external_product_id' => $itemData->product_id,
                'quantity' => $itemData->quantity,
                'price' => $itemData->price->getAmount(),
            ]);
        }
    }
}
