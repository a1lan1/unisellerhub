<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Actions;

use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

readonly class SyncMoySkladStockToMarketplacesAction
{
    public function __construct(
        private MarketplaceClientFactory $marketplaceClient,
        private PushStockToMarketplaceAction $pushStockAction,
        private MarketplaceConnectionRepositoryInterface $connectionRepository,
        private ProductListingRepositoryInterface $productListingRepository
    ) {}

    public function execute(int $organizationId): void
    {
        // 1. Находим подключение МойСклад через репозиторий
        $msConnection = $this->connectionRepository->findByOrganizationAndMarketplace(
            organizationId: $organizationId,
            marketplace: MarketplaceEnum::MOYSKLAD,
            activeOnly: true
        );

        if (! $msConnection instanceof MarketplaceConnection) {
            Log::warning('Active MoySklad connection not found for organization during sync', [
                'organization_id' => $organizationId,
            ]);

            return;
        }

        // 2. Получаем остатки из МойСклад
        $client = $this->marketplaceClient->make($msConnection->marketplace, $msConnection->credentials);
        try {
            $msStocks = $client->getStocks();
        } catch (Exception $exception) {
            Log::error('Failed to get stocks from MoySklad', [
                'organization_id' => $organizationId,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        if ($msStocks->isEmpty()) {
            return;
        }

        // 3. Собираем листинги маркетплейсов через репозиторий
        $skus = $msStocks->pluck('sku')->filter()->toArray();

        $listingsToUpdate = $this->productListingRepository->getByOrganizationMarketplacesAndSkus(
            organizationId: $organizationId,
            marketplaces: [MarketplaceEnum::WB, MarketplaceEnum::OZON],
            skus: $skus
        );

        if ($listingsToUpdate->isEmpty()) {
            return;
        }

        // Eager load necessary relations for inventory updates
        $listingsToUpdate->loadMissing(['product.organization.warehouses']);

        // 4. Обновляем локальные остатки на основе данных из МС
        foreach ($msStocks as $msStock) {
            /** @var StockData $msStock */
            $matchingListings = $listingsToUpdate->where('vendor_code', $msStock->sku);

            foreach ($matchingListings as $listing) {
                $warehouseId = $listing->product->organization->warehouses->first()?->id;

                if ($warehouseId) {
                    $listing->inventory()->updateOrCreate(
                        ['warehouse_id' => $warehouseId],
                        ['quantity' => $msStock->quantity]
                    );
                } else {
                    Log::warning('No warehouse found for organization during stock sync', [
                        'organization_id' => $organizationId,
                        'listing_id' => $listing->id,
                    ]);
                }
            }
        }

        // 5. Пушим обновленные остатки в маркетплейсы
        try {
            $this->pushStockAction->execute($listingsToUpdate);
        } catch (Exception $exception) {
            Log::error('Failed to push synced stocks to marketplaces', [
                'organization_id' => $organizationId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
