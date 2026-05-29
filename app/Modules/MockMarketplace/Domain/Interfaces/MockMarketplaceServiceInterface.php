<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Interfaces;

use App\Modules\MockMarketplace\Domain\Data\AvitoMockItemDetailsDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockItemDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\AvitoMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockAssortmentDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\MsMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockProductDetailsDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockProductListDTO;
use App\Modules\MockMarketplace\Domain\Data\OzonMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockProductDTO;
use App\Modules\MockMarketplace\Domain\Data\WbMockStockDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockOrderDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockProductDTO;
use App\Modules\MockMarketplace\Domain\Data\YandexMockStockDTO;
use Illuminate\Support\Collection;

interface MockMarketplaceServiceInterface
{
    /**
     * @return Collection<int, WbMockStockDTO>
     */
    public function getWbStocks(int $accountId, ?int $warehouseId = null): Collection;

    public function updateWbStocks(int $accountId, string $warehouseId, array $stocks): void;

    /**
     * @return Collection<int, WbMockOrderDTO>
     */
    public function getWbOrders(int $accountId): Collection;

    /**
     * @return Collection<int, WbMockProductDTO>
     */
    public function getWbProducts(int $accountId): Collection;

    public function updateWbPrices(int $accountId, array $prices): void;

    /**
     * @return Collection<int, OzonMockProductListDTO>
     */
    public function getOzonProducts(int $accountId): Collection;

    /**
     * @param  int[]  $productIds
     * @return Collection<int, OzonMockProductDetailsDTO>
     */
    public function getOzonProductDetails(int $accountId, array $productIds): Collection;

    /**
     * @param  int[]  $productIds
     * @return Collection<int, OzonMockStockDTO>
     */
    public function getOzonStocks(int $accountId, array $productIds): Collection;

    public function updateOzonStocks(int $accountId, array $stocks): void;

    public function updateOzonPrices(int $accountId, array $prices): void;

    /**
     * @return Collection<int, OzonMockOrderDTO>
     */
    public function getOzonOrders(int $accountId): Collection;

    public function getYandexCampaigns(): array;

    /**
     * @return Collection<int, YandexMockProductDTO>
     */
    public function getYandexProducts(int $accountId): Collection;

    /**
     * @return Collection<int, YandexMockStockDTO>
     */
    public function getYandexStocks(int $accountId): Collection;

    public function updateYandexStocks(int $accountId, array $skus): void;

    /**
     * @return Collection<int, YandexMockOrderDTO>
     */
    public function getYandexOrders(int $accountId): Collection;

    public function updateYandexPrices(int $accountId, array $offers): void;

    /**
     * @return Collection<int, AvitoMockItemDTO>
     */
    public function getAvitoItems(int $accountId): Collection;

    public function getAvitoItemDetails(int $accountId, int $itemId): AvitoMockItemDetailsDTO;

    /**
     * @return Collection<int, AvitoMockStockDTO>
     */
    public function getAvitoStocks(int $accountId): Collection;

    /**
     * @return Collection<int, AvitoMockOrderDTO>
     */
    public function getAvitoOrders(int $accountId): Collection;

    public function updateAvitoPrice(int $accountId, int $itemId, float $price): void;

    public function getAvitoSelf(): array;

    /**
     * @return Collection<int, MsMockAssortmentDTO>
     */
    public function getMsAssortment(int $accountId): Collection;

    /**
     * @return Collection<int, MsMockStockDTO>
     */
    public function getMsStocks(int $accountId): Collection;

    /**
     * @return Collection<int, MsMockOrderDTO>
     */
    public function getMsOrders(int $accountId): Collection;
}
