<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'user.profile.view', 'user.profile.update',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.manage', 'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $userRole = Role::create(['name' => RoleEnum::USER->value]);
        $userRole->givePermissionTo(['user.profile.view', 'user.profile.update']);

        $adminRole = Role::create(['name' => RoleEnum::ADMIN->value]);
        $adminRole->givePermissionTo(Permission::all());
    }
}
