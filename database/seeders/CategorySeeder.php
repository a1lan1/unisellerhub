<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Product\Domain\Models\Category;
use Illuminate\Database\Seeder;
use Random\RandomException;

class CategorySeeder extends Seeder
{
    /**
     * @throws RandomException
     */
    public function run(): void
    {
        Category::factory()
            ->count(random_int(5, 10))
            ->create()
            ->each(function (Category $category): void {
                $children = Category::factory()
                    ->count(random_int(2, 10))
                    ->create();
                $category->children()
                    ->saveMany($children);
            });
    }
}
