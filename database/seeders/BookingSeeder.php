<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Directory;
use App\Models\JobCategory;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $directory = Directory::where('slug', 'worker')->first();
            $users     = User::whereHas('profiles.roles', fn ($q) => $q->where('name', 'user'))->with('profiles')->get();
            $workers   = Profile::whereHas('roles', fn ($q) => $q->where('name', 'worker'))->with('user', 'jobCategory')->get();

            if (!$directory || $users->isEmpty() || $workers->isEmpty()) {
                $this->command->warn('Skipping BookingSeeder: missing users, workers, or directory.');
                return;
            }

            $statuses = [
                ['status' => 'pending',   'requested_at' => now()->subDays(7),  'accepted_at' => null,                    'completed_at' => null,                    'cancelled_at' => null],
                ['status' => 'accepted',  'requested_at' => now()->subDays(6),  'accepted_at' => now()->subDays(5),       'completed_at' => null,                    'cancelled_at' => null],
                ['status' => 'completed', 'requested_at' => now()->subDays(14), 'accepted_at' => now()->subDays(13),      'completed_at' => now()->subDays(10),      'cancelled_at' => null],
                ['status' => 'cancelled', 'requested_at' => now()->subDays(10), 'accepted_at' => null,                    'completed_at' => null,                    'cancelled_at' => now()->subDays(9)],
                ['status' => 'completed', 'requested_at' => now()->subDays(20), 'accepted_at' => now()->subDays(19),      'completed_at' => now()->subDays(15),      'cancelled_at' => null],
            ];

            $notes = [
                'Please arrive in the morning if possible.',
                'I need this done urgently.',
                'Call me before arriving.',
                null,
                'Bring your own tools.',
            ];

            $count = 0;
            foreach ($users as $user) {
                $userProfile = $user->profiles->first();
                if (!$userProfile) {
                    continue;
                }

                foreach ($workers->random(min(2, $workers->count())) as $workerProfile) {
                    $scenario = $statuses[$count % count($statuses)];

                    Booking::firstOrCreate(
                        [
                            'user_id'    => $user->id,
                            'profile_id' => $workerProfile->id,
                        ],
                        [
                            'directory_id'    => $directory->id,
                            'job_category_id' => $workerProfile->job_category_id,
                            'note'            => $notes[$count % count($notes)],
                            'status'          => $scenario['status'],
                            'requested_at'    => $scenario['requested_at'],
                            'accepted_at'     => $scenario['accepted_at'],
                            'completed_at'    => $scenario['completed_at'],
                            'cancelled_at'    => $scenario['cancelled_at'],
                        ]
                    );

                    $count++;
                }
            }

            $this->command->info("✅ {$count} bookings seeded successfully!");
        });
    }
}
