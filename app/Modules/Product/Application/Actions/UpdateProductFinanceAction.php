<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Actions;

use App\Modules\Product\Domain\Data\UpdateProductFinanceData;
use App\Modules\Product\Domain\Models\ProductListing;
use App\Modules\Product\Domain\Repositories\ProductListingRepositoryInterface;
use Cknow\Money\Money;

class UpdateProductFinanceAction
{
    public function __construct(private readonly ProductListingRepositoryInterface $repository) {}

    /**
     * Update product and listing finance data.
     */
    public function execute(UpdateProductFinanceData $dto): ProductListing
    {
        $listingData = array_filter([
            'commission_percent' => $dto->commissionPercent,
            'logistic_cost' => $dto->logisticCost,
        ], fn (float|Money|null $value): bool => $value !== null);

        return $this->repository->updateFinance(
            $dto->listingId,
            $listingData,
            $dto->costPrice
        );
    }
}
