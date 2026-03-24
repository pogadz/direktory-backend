<?php

namespace Database\Seeders;

use App\Models\Directory;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = $_ENV['SEEDER_ADMIN_EMAIL'] ?? '';
        $password = $_ENV['SEEDER_ADMIN_PASSWORD'] ?? '';

        if($email && $password){
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'firstname' => 'Admin',
                    'lastname' => 'User',
                    'password' => Hash::make($password),
                ]
            );

            $directory = Directory::where('slug', 'worker')->first();

            $profile = Profile::firstOrCreate(
                ['user_id' => $user->id, 'name' => 'Admin Profile'],
                [
                    'is_active' => true,
                    'directory_id' => $directory?->id,
                ]
            );

            $adminRole = Role::where('name', 'admin')->first();

            if ($adminRole) {
                $profile->roles()->syncWithoutDetaching([$adminRole->id]);
            }

            $this->command->info('✅ Admin user created successfully!');
            $this->command->info('Email: ' . $email);
            $this->command->info('Password: ' . $password);
        }else{
            $this->command->info('Please provide both email and password.');
        }
 
    }
}
