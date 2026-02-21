<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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

        User::firstOrCreate(
            ['email' => $email],
            [
                'firstname' => 'Admin',
                'lastname'  => 'User',
                'password'  => Hash::make($password),
            ]
        );

        $this->command->info('✅ Admin user created successfully!');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);
        $this->command->info('Access: full (wildcard token on login)');
    }
}
