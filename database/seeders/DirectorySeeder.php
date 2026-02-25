<?php

namespace Database\Seeders;

use App\Models\Directory;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        Directory::firstOrCreate(
            ['slug' => 'worker'],
            ['name' => 'worker']
        );

        $this->command->info('✅ Directory seeded successfully!');
    }
}
