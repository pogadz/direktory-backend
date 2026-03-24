<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Booking;
use App\Notifications\BookingStatusChanged;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Enums\BookingStatus;

/**
 * @group Booking
 */
class BookingController extends Controller
{
    use AuthorizesRequests;

    protected BookingRepositoryInterface $bookings;

    public function __construct(BookingRepositoryInterface $bookings)
    {
        $this->bookings = $bookings;
    }

    /**
     * List all bookings for the user
     */
    public function index(Request $request)
    {
        $user = $request->user();

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
        $this->authorize('create', Booking::class);

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

        // Notify worker
        $workerProfile = Profile::with('user')->find($request->profile_id);

        if ($workerProfile?->user) {
            $workerProfile->user->notify(
                new BookingStatusChanged($booking, 'pending')
            );
        }

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    /**
     * Get a specific booking
     */
    public function show(Request $request, $id)
    {
        $booking = $this->bookings->find($id);
        $this->authorize('view', $booking);

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * Update a booking
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $this->authorize('update', $booking);

        $booking = $this->bookings->update($id, $request->only('note'));

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking,
        ]);
    }

    /**
     * Set booking status
     */
    public function setStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,completed,cancelled',
        ]);

        $booking = Booking::with('profile.user')->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $status = BookingStatus::from($request->status);

        $this->authorize($status->value, $booking);

        $booking = $this->bookings->setStatus($id, $status->value);

        if (!$booking) {
            return response()->json([
                'message' => 'Invalid booking or status',
            ], 400);
        }

        $booking->load('profile.user');

        if ($booking->profile?->user) {
            $booking->profile->user->notify(
                new BookingStatusChanged($booking, $status->value)
            );
        }

        return response()->json([
            'message' => "Booking status {$booking->status}",
            'booking' => $booking,
        ]);
    }

    /**
     * Archive a booking
     */
    public function archive(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $this->authorize('delete', $booking);

        $this->bookings->archive($id);

        return response()->json([
            'message' => 'Booking archived successfully',
        ]);
    }
}
