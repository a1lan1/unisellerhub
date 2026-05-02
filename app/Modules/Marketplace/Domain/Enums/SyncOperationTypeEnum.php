<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Enums;

enum SyncOperationTypeEnum: string
{
    case Inventory = 'inventory';
    case Orders = 'orders';
    case Products = 'products';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'Inventory',
            self::Orders => 'Orders',
            self::Products => 'Products',
        };
    }
}
