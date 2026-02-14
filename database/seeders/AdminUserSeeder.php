<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create Admin User
            $adminUser = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'firstname' => 'Admin',
                    'lastname' => 'User',
                    'password' => Hash::make('testing123'),
                ]
            );

            // Create Admin Account for this user
            $adminAccount = Account::firstOrCreate(
                [
                    'user_id' => $adminUser->id,
                    'name' => 'Admin Account',
                ],
                [
                    'is_active' => true,
                    'bio' => 'System administrator with full access',
                ]
            );

            // Assign super-admin role to the account
            $superAdminRole = Role::where('name', 'super-admin')->first();
            if ($superAdminRole) {
                $adminAccount->roles()->syncWithoutDetaching([$superAdminRole->id]);
            }

            $this->command->info('✅ Admin user created successfully!');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: testing123');
            $this->command->info('Account ID: ' . $adminAccount->id);
            $this->command->info('Role: super-admin (all permissions)');
        });
    }
}
