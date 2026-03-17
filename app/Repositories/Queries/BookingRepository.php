<?php

namespace App\Repositories\Queries;

use App\Models\Booking;
use App\Models\Profile;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    public function allByUser(int $userId): Collection
    {
        // Get all profile IDs for the user
        $profileIds = Profile::where('user_id', $userId)->pluck('id');

        // Fetch bookings linked either to the user or their profiles
        return Booking::where('user_id', $userId)
            ->orWhereIn('profile_id', $profileIds)
            ->get();
    }

    public function find(int $id): ?Booking
    {
        return Booking::find($id);
    }

    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function update(int $id, array $data): ?Booking
    {
        $booking = Booking::find($id);
        if (!$booking) return null;

        $booking->update($data);
        return $booking;
    }

    public function setStatus(int $id, string $status): ?Booking
    {
        $booking = Booking::find($id);
        if (!$booking) return null;

        $booking->status = $status;

        switch ($status) {
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
                return null;
        }

        $booking->save();

        return $booking;
    }

    public function archive(int $id): bool
    {
        $booking = Booking::find($id);
        if (!$booking) return false;

        return $booking->delete();
    }
}