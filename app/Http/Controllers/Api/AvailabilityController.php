<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;

/**
 * @group Availability
 */
class AvailabilityController extends Controller
{
    protected $availabilityRepository;

    public function __construct(AvailabilityRepositoryInterface $availabilityRepository)
    {
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * Get Available times by profile user
     * 
     * @urlParam profile_id integer required The ID of the profile. Example: 1
     */
    public function getAvailability($profileId)
    {
        $availability = $this->availabilityRepository->getProfile($profileId);

        if (!$availability) {
            return response()->json([
                'message' => 'No schedule found'
            ], 404);
        }

        return response()->json($availability);
    }

    /**
     * Save availability
     * 
     * @bodyParam profile_id integer required The ID of the profile. Example: 1
     * @bodyParam availabilities array required List of availability objects. Example: [{"day":"monday","open":"9:00 AM","close":"5:00 PM","enabled":true}]
     * @bodyParam availabilities.*.day string required The day of the week. Example: "monday".
     * @bodyParam availabilities.*.open string The opening time. Example: "9:00 AM".
     * @bodyParam availabilities.*.close string The closing time. Example: "5:00 PM"
     * @bodyParam availabilities.*.enabled boolean Whether this day is enabled. Example: true
     */
    public function saveAvailability(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|integer',
            'availabilities' => 'required|array',

            'availabilities.*.day' => 'nullable|string',
            'availabilities.*.open' => 'nullable|string',
            'availabilities.*.close' => 'nullable|string',
            'availabilities.*.enabled' => 'nullable|boolean',
        ]);

        $availability = $this->availabilityRepository->save(
            $request->profile_id,
            $request->availabilities
        );

        return response()->json($availability);
    }
}
