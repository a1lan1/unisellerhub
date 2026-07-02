<?php

use App\Modules\Inventory\Domain\Models\Inventory;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\Models\ProductListing;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'products' => Product::count(),
            'listings' => ProductListing::count(),
            'warehouses' => Warehouse::count(),
            'inventory' => Inventory::count(),
        ];
    }
};
