<?php

namespace Database\Seeders;

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
            // Get roles for assignment
            $managerRole = Role::where('name', 'manager')->first();
            $editorRole = Role::where('name', 'editor')->first();
            $viewerRole = Role::where('name', 'viewer')->first();

            // Define 10 normal users
            $users = [
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'profile_name' => 'John\'s Workspace',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Content manager',
                ],
                [
                    'firstname' => 'Jane',
                    'lastname' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'profile_name' => 'Jane\'s Profile',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content editor',
                ],
                [
                    'firstname' => 'Michael',
                    'lastname' => 'Johnson',
                    'email' => 'michael.johnson@example.com',
                    'profile_name' => 'Michael\'s Workspace',
                    'roles' => [$viewerRole?->id],
                    'bio' => 'Content viewer',
                ],
                [
                    'firstname' => 'Emily',
                    'lastname' => 'Brown',
                    'email' => 'emily.brown@example.com',
                    'profile_name' => 'Emily\'s Profile',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content creator and editor',
                ],
                [
                    'firstname' => 'David',
                    'lastname' => 'Wilson',
                    'email' => 'david.wilson@example.com',
                    'profile_name' => 'David\'s Workspace',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Team manager',
                ],
                [
                    'firstname' => 'Sarah',
                    'lastname' => 'Martinez',
                    'email' => 'sarah.martinez@example.com',
                    'profile_name' => 'Sarah\'s Profile',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Blog editor',
                ],
                [
                    'firstname' => 'James',
                    'lastname' => 'Anderson',
                    'email' => 'james.anderson@example.com',
                    'profile_name' => 'James\'s Workspace',
                    'roles' => [$viewerRole?->id],
                    'bio' => 'Read-only access',
                ],
                [
                    'firstname' => 'Lisa',
                    'lastname' => 'Taylor',
                    'email' => 'lisa.taylor@example.com',
                    'profile_name' => 'Lisa\'s Profile',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Project manager',
                ],
                [
                    'firstname' => 'Robert',
                    'lastname' => 'Thomas',
                    'email' => 'robert.thomas@example.com',
                    'profile_name' => 'Robert\'s Workspace',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content writer',
                ],
                [
                    'firstname' => 'Jennifer',
                    'lastname' => 'White',
                    'email' => 'jennifer.white@example.com',
                    'profile_name' => 'Jennifer\'s Profile',
                    'roles' => [$viewerRole?->id],
                    'bio' => 'Guest viewer',
                ],
            ];

            foreach ($users as $userData) {
                // Create user
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'firstname' => $userData['firstname'],
                        'lastname' => $userData['lastname'],
                        'password' => $password,
                    ]
                );

                // Create profile for user
                $profile = Profile::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $userData['profile_name'],
                    ],
                    [
                        'is_active' => true,
                        'bio' => $userData['bio'],
                    ]
                );

                // Assign roles to profile
                if (!empty($userData['roles'])) {
                    $roleIds = array_filter($userData['roles']); // Remove null values
                    if (!empty($roleIds)) {
                        $profile->roles()->syncWithoutDetaching($roleIds);
                    }
                }

                // Get role name for display
                $roleName = 'user';
                if (!empty($userData['roles'])) {
                    $roleId = $userData['roles'][0];
                    if ($roleId == $managerRole?->id) $roleName = 'manager';
                    elseif ($roleId == $editorRole?->id) $roleName = 'editor';
                    elseif ($roleId == $viewerRole?->id) $roleName = 'viewer';
                }

                $this->command->info("Created user: {$userData['email']} ({$roleName})");
            }

            $this->command->info('');
            $this->command->info('✅ 10 normal users created successfully!');
            $this->command->info('All passwords: testing123');
            $this->command->info('');
            $this->command->info('Breakdown:');
            $this->command->info('- 3 Managers');
            $this->command->info('- 4 Editors');
            $this->command->info('- 3 Viewers');
        });
    }
}
