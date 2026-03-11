<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * @group Booking
     * Get all bookings for user profile
     */
    public function index(Request $request)
    {
        $bookings = $request->user()->profiles()->get()->bookings()->get();

        return response()->json([
            'bookings' => $bookings,
            'total' => $bookings->count(),
        ]);
    }

    /**
     * @group Booking
     * Create new booking for user profile
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_id'       => 'required|exists:profiles,id',
            'user_id'          => 'required|exists:users,id',
            'directory_id'     => 'required|exists:directories,id',
            'job_category_id'  => 'required|exists:job_categories,id',
            'note'           => 'nullable|string',
        ]);

        $booking = Booking::create([
            'user_id'         => $request->user_id,
            'profile_id'      => $request->profile_id,
            'directory_id'    => $request->directory_id,
            'job_category_id' => $request->job_category_id,
            'note'          => $request->note,
            'requested_at'     => now(),
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
        $booking = Booking::findOrFail($id);

        $request->validate([
            'id' => 'required|exists:bookings,id',
            'note' => 'nullable|string',
        ]);

        $booking->update([
            'note' => $request->note,
        ]);

        return response()->json
        ([
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
        $booking = Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,accepted,completed,cancelled',
        ]);

        $booking->status = $request->status;

        switch($status){
            case 'pending':
                $booking->requested_at = now();
                break;
            case 'accepted':
                $booking->accepted_at = now();
                break;
            case 'completed':
                $booking->completed_at = now();
                break;
            case 'cancelled':
                $booking->cancelled_at = now();
                break;
            default:
                return response()->json([
                    'message' => 'Invalid booking status',
                ], 400);
        }

        $booking->save();

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
        $booking = Booking::findOrFail($id);

        $booking->delete();

        return response()->json([
            'message' => 'Booking archived successfully',
        ]);
    }
}
