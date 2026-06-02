<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Geo\Domain\Models\Location;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Domain\Models\Review;
use App\Modules\User\Domain\Enums\RoleEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sellers = User::role(RoleEnum::SELLER)->get();

        Location::factory($sellers->count() * 5)
            ->make()
            ->each(function (Location $location) use ($sellers): void {
                $location->user_id = $sellers->random()->id;
                $location->save();

                Review::factory(random_int(5, 20))
                    ->for($location)
                    ->create();
            });

        ResponseTemplate::factory($sellers->count() * 10)
            ->make()
            ->each(function (ResponseTemplate $responseTemplate) use ($sellers): void {
                $responseTemplate->user_id = $sellers->random()->id;
                $responseTemplate->save();
            });
    }
}
