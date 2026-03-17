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
            JobCategoriesSeeder::class,
            DirectorySeeder::class,
            NormalUsersSeeder::class,
            ProfileSeeder::class,
            GallerySeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed!');
    }
}
