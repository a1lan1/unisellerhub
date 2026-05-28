<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Actions;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\ExternalWarehouseId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\ValueObjects\Sku;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

readonly class PushStockToMarketplaceAction
{
    public function __construct(
        private MarketplaceClientFactory $marketplaceClient,
        private MarketplaceConnectionRepositoryInterface $connectionRepository,
    ) {}

    /**
     * Pushes local stock quantities for given product listings to the marketplace.
     *
     * @param  Collection<int, ProductListing>  $listings
     *
     * @throws Exception
     */
    public function execute(Collection $listings): void
    {
        if ($listings->isEmpty()) {
            return;
        }

        $listings->loadMissing(['inventory.warehouse', 'product']);

        $listingsByMarketplace = $listings->groupBy('marketplace.value');

        foreach ($listingsByMarketplace as $marketplaceValue => $marketplaceListings) {
            $marketplace = MarketplaceEnum::tryFrom($marketplaceValue);

            if (! $marketplace) {
                Log::warning('Unknown marketplace encountered during stock push', [
                    'marketplace_value' => $marketplaceValue,
                ]);

                continue;
            }

            // Assuming all listings in the collection belong to the same organization
            $organizationId = $marketplaceListings->first()->product->organization_id;

            $connection = $this->connectionRepository->findByOrganizationAndMarketplace(
                organizationId: $organizationId,
                marketplace: $marketplace,
                activeOnly: true,
            );

            if (! $connection instanceof MarketplaceConnection) {
                Log::info('No active marketplace connection found for stock push', [
                    'organization_id' => $organizationId,
                    'marketplace' => $marketplace->value,
                ]);

                continue;
            }

            $client = $this->marketplaceClient->make($connection->marketplace, $connection->credentials);

            $stocksToPush = collect();

            foreach ($marketplaceListings as $listing) {
                /** @var ProductListing $listing */
                $localInventory = $listing->inventory->first();

                if ($localInventory instanceof Inventory) {
                    $localWarehouse = $localInventory->warehouse;
                    $stocksToPush->push(new StockData(
                        external_product_id: new ExternalProductId($listing->external_id),
                        external_warehouse_id: new ExternalWarehouseId($localWarehouse->external_id->getValue()),
                        quantity: new Quantity($localInventory->quantity->getValue()),
                        sku: $listing->vendor_code ? new Sku($listing->vendor_code) : null,
                    ));
                }
            }

            if ($stocksToPush->isNotEmpty()) {
                try {
                    $client->updateStocks($stocksToPush);
                } catch (Exception $e) {
                    Log::error('Failed to push stock to marketplace', [
                        'marketplace' => $marketplace->value,
                        'organization_id' => $organizationId,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        }
    }
}
