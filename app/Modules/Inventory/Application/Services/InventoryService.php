<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Services;

use App\Modules\Inventory\Domain\Actions\PushStockToMarketplaceAction;
use App\Modules\Inventory\Domain\Data\InventoryFilterData;
use App\Modules\Inventory\Domain\Data\PullBulkInventoryData;
use App\Modules\Inventory\Domain\Data\PullInventoryData;
use App\Modules\Inventory\Domain\Data\StockData;
use App\Modules\Inventory\Domain\Data\SyncMoySkladStockData;
use App\Modules\Inventory\Domain\Interfaces\InventoryServiceInterface;
use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use App\Modules\Inventory\Domain\ValueObjects\ExternalProductId;
use App\Modules\Inventory\Domain\ValueObjects\Quantity;
use App\Modules\Inventory\Infrastructure\Jobs\SyncBulkInventoryJob;
use App\Modules\Inventory\Infrastructure\Jobs\SyncInventoryJob;
use App\Modules\Inventory\Infrastructure\Jobs\SyncMoySkladStockJob;
use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceConnectionRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Factories\MarketplaceClientFactory;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use App\Modules\Product\ValueObjects\Sku;
use App\Modules\User\Domain\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

readonly class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        private InventoryRepositoryInterface $repository,
        private PushStockToMarketplaceAction $pushStockToMarketplaceAction,
        private MarketplaceClientFactory $marketplaceClientFactory,
        private MarketplaceConnectionRepositoryInterface $connectionRepository,
        private ProductListingRepositoryInterface $productListingRepository,
    ) {}

    public function getPaginatedInventory(User $user, InventoryFilterData $filter): LengthAwarePaginator
    {
        if (! $user->has_organization) {
            return new LengthAwarePaginator([], 0, $filter->pagination->getPerPage());
        }

        return $this->repository->getPaginatedInventory($filter);
    }

    public function getInventoryHealthStats(User $user): array
    {
        if (! $user->has_organization) {
            return [
                'out_of_stock' => 0,
                'low_stock' => 0,
                'total_items' => 0,
                'out_of_stock_items' => collect(),
                'low_stock_items' => collect(),
            ];
        }

        $outOfStockItems = $this->repository->getOutOfStockItems();
        $lowStockItems = $this->repository->getLowStockItems();
        $healthStats = $this->repository->getHealthStats();

        return [
            'out_of_stock' => $healthStats['out_of_stock'],
            'low_stock' => $healthStats['low_stock'],
            'total_items' => $healthStats['total_items'],
            'out_of_stock_items' => $outOfStockItems,
            'low_stock_items' => $lowStockItems,
        ];
    }

    /**
     * Централизованное обновление остатка:
     * 1. Пушим в МойСклад (как источник истины)
     * 2. Если успешно -> обновляем в локальной БД
     * 3. Рассылаем по маркетплейсам
     *
     * @throws Exception
     */
    public function updateInventoryAndPushToMarketplace(int $inventoryId, int $quantity): Inventory
    {
        $inventory = $this->repository->findById($inventoryId);
        if (! $inventory instanceof Inventory) {
            Log::error('Inventory not found during updateInventoryAndPushToMarketplace', [
                'inventory_id' => $inventoryId,
            ]);
            throw new RuntimeException('Inventory not found.');
        }

        $inventory->load('listing.product.organization', 'warehouse');
        $organizationId = $inventory->listing->product->organization_id;

        // 1. Находим подключение МойСклад через репозиторий
        $msConnection = $this->connectionRepository->findByOrganizationAndMarketplace(
            organizationId: $organizationId,
            marketplace: MarketplaceEnum::MOYSKLAD,
        );

        if ($msConnection instanceof MarketplaceConnection) {
            $msClient = $this->marketplaceClientFactory->make($msConnection->marketplace, $msConnection->credentials);
            try {
                $msClient->updateStocks(collect([
                    new StockData(
                        external_product_id: new ExternalProductId($inventory->listing->external_id),
                        external_warehouse_id: $inventory->warehouse->external_id,
                        quantity: new Quantity($quantity),
                        sku: $inventory->listing->vendor_code ? new Sku($inventory->listing->vendor_code) : null
                    ),
                ]));
            } catch (Exception $e) {
                Log::error('Failed to push stock to MoySklad', [
                    'inventory_id' => $inventoryId,
                    'organization_id' => $organizationId,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        } else {
            Log::warning('No MoySklad connection found for organization during stock update', [
                'organization_id' => $organizationId,
                'inventory_id' => $inventoryId,
            ]);
        }

        // 2. Обновляем локально
        $this->repository->updateQuantity($inventory, $quantity);

        // 3. Рассылаем по всем МП этой организации через репозиторий
        $listings = $this->productListingRepository->getByOrganizationAndMarketplaces(
            organizationId: $organizationId,
            marketplaces: [MarketplaceEnum::WB, MarketplaceEnum::OZON],
            vendorCode: $inventory->listing->vendor_code
        );

        if ($listings->isNotEmpty()) {
            $this->pushStockToMarketplaceAction->execute($listings);
        }

        return $inventory;
    }

    public function pullInventory(PullInventoryData $dto): void
    {
        dispatch(new SyncInventoryJob($dto->organizationId, $dto->marketplace));
    }

    public function pullBulkInventory(PullBulkInventoryData $dto): void
    {
        SyncBulkInventoryJob::dispatchIf(
            $dto->ids !== [],
            $dto->organizationId, $dto->ids
        );
    }

    public function syncMoySkladStock(SyncMoySkladStockData $dto): void
    {
        dispatch(new SyncMoySkladStockJob($dto->organizationId));
    }
}
