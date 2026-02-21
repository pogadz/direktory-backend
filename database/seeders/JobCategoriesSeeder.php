<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cleaner',
            'Landscaper',
            'Electrician',
            'Roofer',
            'Carpenter',
            'Locksmith',
            'Handyman',
            'Plumber',
            'Gardener',
            'HVAC Technician',
            'Painter',
        ];

        foreach ($categories as $name) {
            JobCategory::firstOrCreate(['name' => $name]);
        }

        $this->command->info('✅ Job categories seeded successfully!');
    }
}
