<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $completedBookings = Booking::where('status', 'completed')->get();

            if ($completedBookings->isEmpty()) {
                $this->command->warn('Skipping ReviewSeeder: no completed bookings found.');
                return;
            }

            $comments = [
                'Great work, very professional and on time.',
                'Did a solid job, would recommend.',
                'Satisfied with the results, will hire again.',
                'Good communication throughout the process.',
                'Exceeded my expectations, excellent service.',
            ];

            $ratings = ['3', '4', '4', '5', '5'];

            $count = 0;
            foreach ($completedBookings as $i => $booking) {
                Review::firstOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'user_id'    => $booking->user_id,
                        'profile_id' => $booking->profile_id,
                        'rating'     => $ratings[$i % count($ratings)],
                        'comment'    => $comments[$i % count($comments)],
                    ]
                );

                $count++;
            }

            $this->command->info("✅ {$count} reviews seeded successfully!");
        });
    }
}
