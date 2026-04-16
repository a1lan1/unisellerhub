<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Organization $organization */
        $organization = Organization::factory()->create([
            'name' => 'Default UniSellerHub Org',
            'slug' => 'default-unisellerhub-org',
        ]);

        User::query()->update(['organization_id' => $organization->id]);
    }
}
