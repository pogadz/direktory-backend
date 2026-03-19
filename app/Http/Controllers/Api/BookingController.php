<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BookingRepositoryInterface;

/**
 * @group Booking
 */
class BookingController extends Controller
{
    protected $bookings;

    public function __construct(BookingRepositoryInterface $bookings)
    {
        $this->bookings = $bookings;
    }

    /**
     * Get all bookings for the authenticated user
     *
     * @queryParam profile_id integer Filter by profile ID. Example: 1
     * @queryParam directory_id integer Filter by directory ID. Example: 1
     * @queryParam job_category_id integer Filter by job category ID. Example: 1
     * @queryParam status string Filter by booking status (pending, accepted, completed, cancelled). Example: pending
     * @queryParam requested_at_from date Filter bookings requested after this date format(2026-01-01). Example: "".
     * @queryParam requested_at_to date Filter bookings requested before this date. Example: "".
     * @queryParam accepted_at_from date Filter bookings accepted after this date. Example: "".
     * @queryParam accepted_at_to date Filter bookings accepted before this date. Example: "".
     * @queryParam completed_at_from date Filter bookings completed after this date. Example: "".
     * @queryParam completed_at_to date Filter bookings completed before this date. Example: "".
     * @queryParam cancelled_at_from date Filter bookings cancelled after this date. Example: "".
     * @queryParam cancelled_at_to date Filter bookings cancelled before this date. Example: "".
     * @queryParam created_at_from date Filter bookings created after this date. Example: "".
     * @queryParam created_at_to date Filter bookings created before this date. Example: "".
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Only allow filtering fields
        $filters = $request->only([
            'profile_id',
            'directory_id',
            'job_category_id',
            'status',
            'requested_at_from', 'requested_at_to',
            'accepted_at_from', 'accepted_at_to',
            'completed_at_from', 'completed_at_to',
            'cancelled_at_from', 'cancelled_at_to',
            'created_at_from', 'created_at_to',
        ]);

        $bookings = $this->bookings->allByUser($user->id, $filters);

        return response()->json([
            'bookings' => $bookings,
            'total'    => $bookings->count(),
        ]);
    }

    /**
     * Create new booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_id'       => 'required|exists:profiles,id',
            'directory_id'     => 'required|exists:directories,id',
            'job_category_id'  => 'required|exists:job_categories,id',
            'note'             => 'nullable|string',
        ]);

        $user = $request->user();

        $booking = $this->bookings->create([
            'user_id'         => $user->id,
            'profile_id'      => $request->profile_id,
            'directory_id'    => $request->directory_id,
            'job_category_id' => $request->job_category_id,
            'note'            => $request->note,
            'requested_at'    => now(),
            'status'          => 'pending'
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $booking = $this->bookings->update($id, $request->only('note'));

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking,
        ]);
    }

    /**
     * Set booking status
     */
    public function setStatus(Request $request, $id, $status = 'pending')
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,completed,cancelled',
        ]);

        $booking = $this->bookings->setStatus($id, $request->status);

        if (!$booking) {
            return response()->json([
                'message' => 'Invalid booking or status',
            ], 400);
        }

        return response()->json([
            'message' => 'Booking status updated successfully',
            'booking' => $booking,
        ]);
    }

    /**
     * Archive booking
     */
    public function archive(Request $request, $id)
    {
        $deleted = $this->bookings->archive($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Booking archived successfully',
        ]);
    }
}
