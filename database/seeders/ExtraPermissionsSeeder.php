<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions for features added after ShieldSeeder's role export was generated.
 * Idempotent: safe to run on an existing database as well as after a fresh seed.
 */
class ExtraPermissionsSeeder extends Seeder
{
    /** @var array<string, list<string>> role => permissions */
    public const GRANTS = [
        'Admin' => [
            'view_holiday', 'view_any_holiday', 'create_holiday', 'update_holiday', 'delete_holiday', 'delete_any_holiday',
        ],
        'HR Manager' => [
            'view_holiday', 'view_any_holiday', 'create_holiday', 'update_holiday', 'delete_holiday', 'delete_any_holiday',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (self::GRANTS as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            foreach ($permissions as $name) {
                $permission = Permission::findOrCreate($name, 'web');
                $role?->givePermissionTo($permission);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
