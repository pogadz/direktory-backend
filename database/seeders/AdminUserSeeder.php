<?php

namespace Database\Seeders;

use App\Models\Profile;
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
        $email = $_ENV['SEEDER_ADMIN_EMAIL'] ?? 'admin@example.com';
        $password = $_ENV['SEEDER_ADMIN_PASSWORD'] ?? 'testing123';

        DB::transaction(function () use ($email, $password) {
            // Create Admin User
            $adminUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'firstname' => 'Admin',
                    'lastname' => 'User',
                    'password' => Hash::make($password),
                ]
            );

            // Create Admin Profile for this user
            $adminProfile = Profile::firstOrCreate(
                [
                    'user_id' => $adminUser->id,
                    'name' => 'Admin Profile',
                ],
                [
                    'is_active' => true,
                    'bio' => 'System administrator with full access',
                ]
            );

            // Assign super-admin role to the profile
            $superAdminRole = Role::where('name', 'super-admin')->first();
            if ($superAdminRole) {
                $adminProfile->roles()->syncWithoutDetaching([$superAdminRole->id]);
            }

            $this->command->info('✅ Admin user created successfully!');
            $this->command->info('Email: ' . $email);
            $this->command->info('Password: ' . $password);
            $this->command->info('Profile ID: ' . $adminProfile->id);
            $this->command->info('Role: super-admin (all permissions)');
        });
    }
}
