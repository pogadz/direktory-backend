<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create Permissions
            $permissions = [
                // User permissions
                ['name' => 'user.view',   'display_name' => 'View User',   'description' => 'View user profiles',   'category' => 'user'],
                ['name' => 'user.edit',   'display_name' => 'Edit User',   'description' => 'Edit user profiles',   'category' => 'user'],
                ['name' => 'user.update', 'display_name' => 'Update User', 'description' => 'Update user profiles', 'category' => 'user'],
                ['name' => 'user.delete', 'display_name' => 'Delete User', 'description' => 'Delete user profiles', 'category' => 'user'],

                // Worker permissions
                ['name' => 'worker.view',   'display_name' => 'View Worker',   'description' => 'View worker profiles',   'category' => 'worker'],
                ['name' => 'worker.edit',   'display_name' => 'Edit Worker',   'description' => 'Edit worker profiles',   'category' => 'worker'],
                ['name' => 'worker.update', 'display_name' => 'Update Worker', 'description' => 'Update worker profiles', 'category' => 'worker'],
                ['name' => 'worker.delete', 'display_name' => 'Delete Worker', 'description' => 'Delete worker profiles', 'category' => 'worker'],

                // Admin permissions (for middleware checks)
                ['name' => 'manage-roles',       'display_name' => 'Manage Roles',       'description' => 'Create, edit, and delete roles',       'category' => 'admin'],
                ['name' => 'manage-permissions', 'display_name' => 'Manage Permissions', 'description' => 'Create, edit, and delete permissions', 'category' => 'admin'],
            ];

            $createdPermissions = [];
            foreach ($permissions as $permission) {
                $createdPermissions[$permission['name']] = Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    $permission
                );
            }

            // --- Roles ---

            $admin = Role::firstOrCreate(
                ['name' => 'admin'],
                [
                    'display_name' => 'Admin',
                    'description'  => 'Full system access',
                    'is_system_role' => true,
                ]
            );

            $user = Role::firstOrCreate(
                ['name' => 'user'],
                [
                    'display_name' => 'User',
                    'description'  => 'Regular platform user',
                    'is_system_role' => false,
                ]
            );

            $worker = Role::firstOrCreate(
                ['name' => 'worker'],
                [
                    'display_name' => 'Worker',
                    'description'  => 'Service worker with a public profile',
                    'is_system_role' => false,
                ]
            );

            // Admin gets all permissions
            $admin->permissions()->sync(Permission::all()->pluck('id'));

            // User gets user.* permissions
            $user->permissions()->sync(
                Permission::where('category', 'user')->pluck('id')
            );

            // Worker gets worker.* permissions
            $worker->permissions()->sync(
                Permission::where('category', 'worker')->pluck('id')
            );

            $this->command->info('✅ Roles and Permissions seeded successfully!');
            $this->command->info('Created ' . count($permissions) . ' permissions');
            $this->command->info('Created 3 roles: admin, user, worker');
        });
    }
}
