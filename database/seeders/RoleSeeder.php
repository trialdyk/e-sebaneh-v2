<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Post management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',

            // Transaction management
            'view transactions',
            'create transactions',
            'approve transactions',

            // Settings
            'view settings',
            'edit settings',

            // Dashboard
            'view dashboard',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value]);

            // Assign permissions based on role
            match ($roleEnum) {
                RoleEnum::SUPERADMIN => $role->syncPermissions(Permission::all()),

                RoleEnum::ADMINPONDOK => $role->syncPermissions([
                    'view users', 'create users', 'edit users',
                    'view posts', 'create posts', 'edit posts', 'delete posts',
                    'view transactions', 'create transactions', 'approve transactions',
                    'view dashboard', 'view reports', 'view settings',
                ]),

                RoleEnum::ADMINPPOB => $role->syncPermissions([
                    'view transactions', 'create transactions', 'approve transactions',
                    'view dashboard', 'view reports',
                ]),

                RoleEnum::TEACHER => $role->syncPermissions([
                    'view posts', 'create posts', 'edit posts',
                    'view dashboard',
                ]),

                RoleEnum::STUDENT, RoleEnum::PARENT => $role->syncPermissions([
                    'view posts',
                ]),
            };
        }
    }
}
