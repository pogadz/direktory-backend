<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NormalUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make($_ENV['SEEDER_PASSWORD'] ?? '');

        DB::transaction(function () use ($password) {
            $userRole   = Role::where('name', 'user')->first();
            $workerRole = Role::where('name', 'worker')->first();

            $categories = JobCategory::pluck('id', 'name');

            $users = [
                [
                    'firstname'    => 'John',
                    'lastname'     => 'Doe',
                    'email'        => 'john.doe@example.com',
                    'profile_name' => "John's Workspace",
                    'role'         => 'worker',
                    'bio'          => 'Experienced handyman',
                    'job_category' => 'Handyman',
                ],
                [
                    'firstname'    => 'Jane',
                    'lastname'     => 'Smith',
                    'email'        => 'jane.smith@example.com',
                    'profile_name' => "Jane's Profile",
                    'role'         => 'worker',
                    'bio'          => 'Professional cleaner',
                    'job_category' => 'Cleaner',
                ],
                [
                    'firstname'    => 'Michael',
                    'lastname'     => 'Johnson',
                    'email'        => 'michael.johnson@example.com',
                    'profile_name' => "Michael's Workspace",
                    'role'         => 'worker',
                    'bio'          => 'Licensed electrician',
                    'job_category' => 'Electrician',
                ],
                [
                    'firstname'    => 'Emily',
                    'lastname'     => 'Brown',
                    'email'        => 'emily.brown@example.com',
                    'profile_name' => "Emily's Profile",
                    'role'         => 'worker',
                    'bio'          => 'Interior and exterior painter',
                    'job_category' => 'Painter',
                ],
                [
                    'firstname'    => 'David',
                    'lastname'     => 'Wilson',
                    'email'        => 'david.wilson@example.com',
                    'profile_name' => "David's Workspace",
                    'role'         => 'worker',
                    'bio'          => 'Certified plumber',
                    'job_category' => 'Plumber',
                ],
                [
                    'firstname'    => 'Sarah',
                    'lastname'     => 'Martinez',
                    'email'        => 'sarah.martinez@example.com',
                    'profile_name' => "Sarah's Profile",
                    'role'         => 'user',
                    'bio'          => 'Looking for landscaping services',
                    'job_category' => 'Landscaper',
                ],
                [
                    'firstname'    => 'James',
                    'lastname'     => 'Anderson',
                    'email'        => 'james.anderson@example.com',
                    'profile_name' => "James's Workspace",
                    'role'         => 'user',
                    'bio'          => 'Homeowner needing repairs',
                    'job_category' => 'Carpenter',
                ],
                [
                    'firstname'    => 'Lisa',
                    'lastname'     => 'Taylor',
                    'email'        => 'lisa.taylor@example.com',
                    'profile_name' => "Lisa's Profile",
                    'role'         => 'user',
                    'bio'          => 'Looking for roofing services',
                    'job_category' => 'Roofer',
                ],
                [
                    'firstname'    => 'Robert',
                    'lastname'     => 'Thomas',
                    'email'        => 'robert.thomas@example.com',
                    'profile_name' => "Robert's Workspace",
                    'role'         => 'worker',
                    'bio'          => 'Emergency locksmith',
                    'job_category' => 'Locksmith',
                ],
                [
                    'firstname'    => 'Jennifer',
                    'lastname'     => 'White',
                    'email'        => 'jennifer.white@example.com',
                    'profile_name' => "Jennifer's Profile",
                    'role'         => 'user',
                    'bio'          => 'Looking for gardening help',
                    'job_category' => 'Gardener',
                ],
            ];

            foreach ($users as $userData) {
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'firstname' => $userData['firstname'],
                        'lastname'  => $userData['lastname'],
                        'password'  => $password,
                    ]
                );

                $profile = Profile::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name'    => $userData['profile_name'],
                    ],
                    [
                        'is_active'       => true,
                        'bio'             => $userData['bio'],
                        'job_category_id' => $categories[$userData['job_category']] ?? null,
                    ]
                );

                $role = $userData['role'] === 'worker' ? $workerRole : $userRole;
                if ($role) {
                    $profile->roles()->syncWithoutDetaching([$role->id]);
                }

                $this->command->info("Created {$userData['role']}: {$userData['email']}");
            }

            $this->command->info('');
            $this->command->info('✅ 10 users created successfully!');
            $this->command->info('All passwords: testing123');
            $this->command->info('');
            $this->command->info('Breakdown:');
            $this->command->info('- 5 Workers');
            $this->command->info('- 4 Users');
        });
    }
}
