<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Enums;

enum MarketplaceEnum: string
{
    case WB = 'wb';
    case OZON = 'ozon';
    case MOYSKLAD = 'ms';
    case YANDEX = 'yandex';
    case AVITO = 'avito';

    public function label(): string
    {
        return match ($this) {
            self::WB => 'Wildberries',
            self::OZON => 'Ozon',
            self::MOYSKLAD => 'MoySklad',
            self::YANDEX => 'Yandex Market',
            self::AVITO => 'Avito',
        };
    }
}
