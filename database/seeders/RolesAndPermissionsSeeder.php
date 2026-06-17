<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = PermissionRegistry::GUARD;

        foreach (PermissionRegistry::all() as $permission) {
            \App\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        foreach (SystemRole::cases() as $systemRole) {
            $role = \App\Models\Role::firstOrCreate([
                'name' => $systemRole->value,
                'guard_name' => $guard,
            ]);

            $role->syncPermissions(PermissionRegistry::forRole($systemRole->value));
        }

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'superadmin@ksrelife.com'],
            [
                'name' => 'Super Administrator',
                'mobile' => '+966500000000',
                'gender' => 'male',
                'status' => 'active',
                'password' => env('SUPER_ADMIN_PASSWORD', 'ks123456relife'),
            ]
        );

        $superAdmin->syncRoles([SystemRole::SuperAdmin->value]);
    }
}
