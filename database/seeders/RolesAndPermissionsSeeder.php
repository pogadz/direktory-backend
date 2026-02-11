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
                // User Management
                ['name' => 'view-users', 'display_name' => 'View Users', 'description' => 'View user list and details', 'category' => 'user-management'],
                ['name' => 'create-users', 'display_name' => 'Create Users', 'description' => 'Create new users', 'category' => 'user-management'],
                ['name' => 'edit-users', 'display_name' => 'Edit Users', 'description' => 'Edit existing users', 'category' => 'user-management'],
                ['name' => 'delete-users', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'category' => 'user-management'],

                // Role & Permission Management
                ['name' => 'view-roles', 'display_name' => 'View Roles', 'description' => 'View roles and permissions', 'category' => 'role-management'],
                ['name' => 'manage-roles', 'display_name' => 'Manage Roles', 'description' => 'Create, edit, and delete roles', 'category' => 'role-management'],
                ['name' => 'manage-permissions', 'display_name' => 'Manage Permissions', 'description' => 'Create, edit, and delete permissions', 'category' => 'role-management'],
                ['name' => 'assign-roles', 'display_name' => 'Assign Roles', 'description' => 'Assign roles to accounts', 'category' => 'role-management'],

                // Content Management
                ['name' => 'view-content', 'display_name' => 'View Content', 'description' => 'View content', 'category' => 'content-management'],
                ['name' => 'create-content', 'display_name' => 'Create Content', 'description' => 'Create new content', 'category' => 'content-management'],
                ['name' => 'edit-content', 'display_name' => 'Edit Content', 'description' => 'Edit existing content', 'category' => 'content-management'],
                ['name' => 'delete-content', 'display_name' => 'Delete Content', 'description' => 'Delete content', 'category' => 'content-management'],
                ['name' => 'publish-content', 'display_name' => 'Publish Content', 'description' => 'Publish content', 'category' => 'content-management'],

                // Dashboard & Reports
                ['name' => 'view-dashboard', 'display_name' => 'View Dashboard', 'description' => 'Access dashboard', 'category' => 'dashboard'],
                ['name' => 'view-reports', 'display_name' => 'View Reports', 'description' => 'View reports and analytics', 'category' => 'dashboard'],
                ['name' => 'export-data', 'display_name' => 'Export Data', 'description' => 'Export data to various formats', 'category' => 'dashboard'],

                // Settings
                ['name' => 'view-settings', 'display_name' => 'View Settings', 'description' => 'View system settings', 'category' => 'settings'],
                ['name' => 'manage-settings', 'display_name' => 'Manage Settings', 'description' => 'Update system settings', 'category' => 'settings'],
            ];

            $createdPermissions = [];
            foreach ($permissions as $permission) {
                $createdPermissions[$permission['name']] = Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    $permission
                );
            }

            // Create System Roles
            $superAdmin = Role::firstOrCreate(
                ['name' => 'super-admin'],
                [
                    'display_name' => 'Super Administrator',
                    'description' => 'Full system access with all permissions',
                    'is_system_role' => true,
                ]
            );

            $admin = Role::firstOrCreate(
                ['name' => 'administrator'],
                [
                    'display_name' => 'Administrator',
                    'description' => 'Administrative access to most features',
                    'is_system_role' => true,
                ]
            );

            $manager = Role::firstOrCreate(
                ['name' => 'manager'],
                [
                    'display_name' => 'Manager',
                    'description' => 'Manage content and view reports',
                    'is_system_role' => false,
                ]
            );

            $editor = Role::firstOrCreate(
                ['name' => 'editor'],
                [
                    'display_name' => 'Editor',
                    'description' => 'Create and edit content',
                    'is_system_role' => false,
                ]
            );

            $viewer = Role::firstOrCreate(
                ['name' => 'viewer'],
                [
                    'display_name' => 'Viewer',
                    'description' => 'Read-only access',
                    'is_system_role' => false,
                ]
            );

            // Assign all permissions to Super Admin
            $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

            // Assign permissions to Administrator
            $adminPermissions = [
                'view-users', 'create-users', 'edit-users', 'delete-users',
                'view-roles', 'manage-roles', 'assign-roles',
                'view-content', 'create-content', 'edit-content', 'delete-content', 'publish-content',
                'view-dashboard', 'view-reports', 'export-data',
                'view-settings', 'manage-settings',
            ];
            $admin->permissions()->sync(
                Permission::whereIn('name', $adminPermissions)->pluck('id')
            );

            // Assign permissions to Manager
            $managerPermissions = [
                'view-users',
                'view-roles',
                'view-content', 'create-content', 'edit-content', 'delete-content', 'publish-content',
                'view-dashboard', 'view-reports', 'export-data',
                'view-settings',
            ];
            $manager->permissions()->sync(
                Permission::whereIn('name', $managerPermissions)->pluck('id')
            );

            // Assign permissions to Editor
            $editorPermissions = [
                'view-content', 'create-content', 'edit-content',
                'view-dashboard',
            ];
            $editor->permissions()->sync(
                Permission::whereIn('name', $editorPermissions)->pluck('id')
            );

            // Assign permissions to Viewer
            $viewerPermissions = [
                'view-content',
                'view-dashboard',
            ];
            $viewer->permissions()->sync(
                Permission::whereIn('name', $viewerPermissions)->pluck('id')
            );

            $this->command->info('✅ Roles and Permissions seeded successfully!');
            $this->command->info('Created ' . count($permissions) . ' permissions');
            $this->command->info('Created 5 roles: super-admin, administrator, manager, editor, viewer');
        });
    }
}
