<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Domain\Models\OrderItem;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Random\RandomException;

class SampleSalesSeeder extends Seeder
{
    /**
     * @throws RandomException
     */
    public function run(): void
    {
        if (! $organization = Organization::first()) {
            $this->command->error('No organization found. Please run DatabaseSeeder first.');

            return;
        }

        resolve(TenantManager::class)->setOrganizationId($organization->id);

        $listings = ProductListing::whereHas('product', fn ($q) => $q->where('organization_id', $organization->id))->get();

        if ($listings->isEmpty()) {
            $this->command->error('No product listings found for the organization.');

            return;
        }

        $this->command->info('Seeding sample sales data for ABC Analysis...');

        // We will create orders for the last 30 days
        for ($i = 0; $i < 50; $i++) {
            $order = Order::create([
                'organization_id' => $organization->id,
                'marketplace' => $listings->random()->marketplace,
                'external_id' => 'ORD-'.strtoupper(bin2hex(random_bytes(4))),
                'status' => 'delivered',
                'total_price' => 0,
                'order_date' => now()->subDays(random_int(0, 30)),
                'last_synced_at' => now(),
            ]);

            $totalPrice = $this->createOrderItems(random_int(1, 3), $order->id, $listings);

            $order->update(['total_price' => $totalPrice]);
        }

        $this->command->info('Successfully seeded 50 orders with items.');
    }

    /**
     * @param  Collection<ProductListing>  $listings
     *
     * @throws RandomException
     */
    private function createOrderItems(int $itemsCount, int $orderId, Collection $listings): int
    {
        $totalPrice = 0;

        for ($j = 0; $j < $itemsCount; $j++) {
            $qty = random_int(1, 5);
            /** @var ProductListing $listing */
            $listing = $listings->random();
            $price = $listing->price->getAmount() ?: random_int(500, 5000);

            OrderItem::create([
                'order_id' => $orderId,
                'product_listing_id' => $listing->id,
                'external_product_id' => $listing->external_id,
                'quantity' => $qty,
                'price' => $price,
            ]);

            $totalPrice += ($price * $qty);
        }

        return $totalPrice;
    }
}
