<?php

namespace Database\Seeders;

use App\Models\Account;
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
        DB::transaction(function () {
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
                    'account_name' => 'John\'s Workspace',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Content manager',
                ],
                [
                    'firstname' => 'Jane',
                    'lastname' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'account_name' => 'Jane\'s Account',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content editor',
                ],
                [
                    'firstname' => 'Michael',
                    'lastname' => 'Johnson',
                    'email' => 'michael.johnson@example.com',
                    'account_name' => 'Michael\'s Workspace',
                    'roles' => [$viewerRole?->id],
                    'bio' => 'Content viewer',
                ],
                [
                    'firstname' => 'Emily',
                    'lastname' => 'Brown',
                    'email' => 'emily.brown@example.com',
                    'account_name' => 'Emily\'s Account',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content creator and editor',
                ],
                [
                    'firstname' => 'David',
                    'lastname' => 'Wilson',
                    'email' => 'david.wilson@example.com',
                    'account_name' => 'David\'s Workspace',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Team manager',
                ],
                [
                    'firstname' => 'Sarah',
                    'lastname' => 'Martinez',
                    'email' => 'sarah.martinez@example.com',
                    'account_name' => 'Sarah\'s Account',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Blog editor',
                ],
                [
                    'firstname' => 'James',
                    'lastname' => 'Anderson',
                    'email' => 'james.anderson@example.com',
                    'account_name' => 'James\'s Workspace',
                    'roles' => [$viewerRole?->id],
                    'bio' => 'Read-only access',
                ],
                [
                    'firstname' => 'Lisa',
                    'lastname' => 'Taylor',
                    'email' => 'lisa.taylor@example.com',
                    'account_name' => 'Lisa\'s Account',
                    'roles' => [$managerRole?->id],
                    'bio' => 'Project manager',
                ],
                [
                    'firstname' => 'Robert',
                    'lastname' => 'Thomas',
                    'email' => 'robert.thomas@example.com',
                    'account_name' => 'Robert\'s Workspace',
                    'roles' => [$editorRole?->id],
                    'bio' => 'Content writer',
                ],
                [
                    'firstname' => 'Jennifer',
                    'lastname' => 'White',
                    'email' => 'jennifer.white@example.com',
                    'account_name' => 'Jennifer\'s Account',
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
                        'password' => Hash::make('testing123'),
                    ]
                );

                // Create account for user
                $account = Account::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $userData['account_name'],
                    ],
                    [
                        'is_active' => true,
                        'bio' => $userData['bio'],
                    ]
                );

                // Assign roles to account
                if (!empty($userData['roles'])) {
                    $roleIds = array_filter($userData['roles']); // Remove null values
                    if (!empty($roleIds)) {
                        $account->roles()->syncWithoutDetaching($roleIds);
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
