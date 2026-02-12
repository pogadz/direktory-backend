<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            NormalUsersSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed!');
        $this->command->info('');
        $this->command->info('Quick start:');
        $this->command->info('1. Login as admin: admin@example.com / testing123');
        $this->command->info('2. Or login as any user: john.doe@example.com / testing123');
        $this->command->info('3. Check SETUP_INSTRUCTIONS.md for next steps');
    }
}
