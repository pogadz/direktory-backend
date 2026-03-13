<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * @group Availability
     * Get Available times by profile user
     */
    public function getAvailability($profileId)
    {
        $availability = Availability::where('profile_id', $profileId)->first();

        if (!$availability) {
            return response()->json([
                'message' => 'No schedule found'
            ], 404);
        }

        return response()->json($availability->schedule);
    }

    /**
     * @group Availability
     * Save availability
     * 
     */
    public function saveAvailability(Request $request)
    {
        // Request body {
        //     profile_id: 1,
        //     availabilities: [
        //         {
        //            day: "monday",
        //            open: "9:00 AM",
        //            close: "5:00 PM",
        //            enabled: true
        //         }
        //     ]
        // }

        $request->validate([
            'profile_id' => 'required|integer',
            'availabilities' => 'required|array'
        ]);

        $schedule = [];

        foreach ($request->availabilities as $dayData) {
            $schedule[$dayData['day']] = [
                'open' => $dayData['open'] ?? null,
                'close' => $dayData['close'] ?? null,
                'enabled' => $dayData['enabled'] ?? false
            ];
        }

        $availabilities = Availability::updateOrCreate(
            ['profile_id' => $request->profile_id],
            ['schedule' => $schedule]
        );

        return response()->json([
            'message' => 'Availability saved',
            'data' => $availabilities
        ]);
    }
}
