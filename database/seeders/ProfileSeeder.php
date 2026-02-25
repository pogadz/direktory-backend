<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $workerRole = Role::where('name', 'worker')->first();
            $userRole   = Role::where('name', 'user')->first();
            $categories = JobCategory::pluck('id', 'name');

            $profiles = [
                [
                    'email'        => 'john.doe@example.com',
                    'profile_name' => "John's Workspace",
                    'bio'          => 'Experienced handyman with 10+ years fixing homes.',
                    'job_category' => 'Handyman',
                    'address'      => '12 Oak Street, Austin, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'jane.smith@example.com',
                    'profile_name' => "Jane's Cleaning Services",
                    'bio'          => 'Professional cleaner specializing in deep cleans and move-outs.',
                    'job_category' => 'Cleaner',
                    'address'      => '45 Maple Ave, Austin, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'michael.johnson@example.com',
                    'profile_name' => "Michael's Electrical",
                    'bio'          => 'Licensed electrician for residential and commercial work.',
                    'job_category' => 'Electrician',
                    'address'      => '88 Birch Blvd, Dallas, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'emily.brown@example.com',
                    'profile_name' => "Emily's Paint Studio",
                    'bio'          => 'Interior and exterior painter with an eye for detail.',
                    'job_category' => 'Painter',
                    'address'      => '23 Elm Road, Houston, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'david.wilson@example.com',
                    'profile_name' => "David's Plumbing",
                    'bio'          => 'Certified plumber, available for emergency callouts.',
                    'job_category' => 'Plumber',
                    'address'      => '7 Cedar Lane, San Antonio, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'sarah.martinez@example.com',
                    'profile_name' => "Sarah's Profile",
                    'bio'          => 'Looking for reliable landscaping services.',
                    'job_category' => 'Landscaper',
                    'address'      => '19 Willow Way, Austin, TX',
                    'role'         => 'user',
                ],
                [
                    'email'        => 'james.anderson@example.com',
                    'profile_name' => "James's Profile",
                    'bio'          => 'Homeowner in need of carpentry repairs.',
                    'job_category' => 'Carpenter',
                    'address'      => '54 Pinewood Dr, Plano, TX',
                    'role'         => 'user',
                ],
                [
                    'email'        => 'lisa.taylor@example.com',
                    'profile_name' => "Lisa's Profile",
                    'bio'          => 'Looking for roofing services after storm damage.',
                    'job_category' => 'Roofer',
                    'address'      => '31 Spruce St, Fort Worth, TX',
                    'role'         => 'user',
                ],
                [
                    'email'        => 'robert.thomas@example.com',
                    'profile_name' => "Robert's Locksmith",
                    'bio'          => 'Emergency locksmith, 24/7 availability.',
                    'job_category' => 'Locksmith',
                    'address'      => '66 Ash Ave, El Paso, TX',
                    'role'         => 'worker',
                ],
                [
                    'email'        => 'jennifer.white@example.com',
                    'profile_name' => "Jennifer's Profile",
                    'bio'          => 'Looking for experienced gardening help.',
                    'job_category' => 'Gardener',
                    'address'      => '3 Fern Circle, Arlington, TX',
                    'role'         => 'user',
                ],
            ];

            foreach ($profiles as $data) {
                $user = User::where('email', $data['email'])->first();

                if (!$user) {
                    $this->command->warn("User {$data['email']} not found, skipping.");
                    continue;
                }

                $profile = Profile::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $data['profile_name']],
                    [
                        'bio'             => $data['bio'],
                        'address'         => $data['address'],
                        'job_category_id' => $categories[$data['job_category']] ?? null,
                        'is_active'       => true,
                    ]
                );

                $role = $data['role'] === 'worker' ? $workerRole : $userRole;
                if ($role) {
                    $profile->roles()->syncWithoutDetaching([$role->id]);
                }

                $this->command->info("Seeded profile: {$data['profile_name']}");
            }

            $this->command->info('');
            $this->command->info('✅ Profiles seeded successfully!');
        });
    }
}
