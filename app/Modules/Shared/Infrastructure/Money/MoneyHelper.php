<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Money;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Cknow\Money\Money;

final class MoneyHelper
{
    /**
     * Converts various marketplace price formats into kopeks (integer).
     *
     * Based on MARKETPLACE_MONEY.md specification:
     * - WB: int (kopeks)
     * - Yandex: int (kopeks)
     * - Ozon: string (rubles, "1234.56")
     * - Avito: mixed (usually string "1234.56")
     * - MoySklad: int (kopeks)
     */
    public static function toKopeks(mixed $value, MarketplaceEnum $marketplace): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return match ($marketplace) {
            MarketplaceEnum::WB,
            MarketplaceEnum::YANDEX,
            MarketplaceEnum::MOYSKLAD => (int) $value,
            MarketplaceEnum::OZON,
            MarketplaceEnum::AVITO => (int) round((float) $value * 100),
        };
    }

    /**
     * Formats Money object back to the format expected by the marketplace API.
     */
    public static function formatForApi(Money $money, MarketplaceEnum $marketplace): string|int
    {
        return match ($marketplace) {
            MarketplaceEnum::WB,
            MarketplaceEnum::YANDEX,
            MarketplaceEnum::MOYSKLAD => (int) $money->getAmount(),
            MarketplaceEnum::OZON,
            MarketplaceEnum::AVITO => $money->formatByDecimal(),
        };
    }

    /**
     * Create a Money object from marketplace raw value.
     */
    public static function fromMarketplace(mixed $value, MarketplaceEnum $marketplace, string $currency = 'RUB'): Money
    {
        $kopeks = self::toKopeks($value, $marketplace);

        return Money::{$currency}($kopeks);
    }
}
