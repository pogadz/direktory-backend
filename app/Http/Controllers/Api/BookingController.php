<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingController extends Controller
{
    protected $bookings;

    public function __construct(BookingRepositoryInterface $bookings)
    {
        $this->bookings = $bookings;
    }

    /**
     * @group Booking
     * Get all bookings
     */
    public function index(Request $request)
    {
        $bookings = $this->bookings->allByUser($request->user()->id);

        return response()->json([
            'bookings' => $bookings,
            'total'    => $bookings->count(),
        ]);
    }

    /**
     * @group Booking
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
     * @group Booking
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
     * @group Booking
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
     * @group Booking
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
