<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function create(User $user): bool
    {
        // Only users without profiles can create bookings
        return $user->profiles()->count() === 0;
    }

    public function update(User $user, Booking $booking): bool
    {
        // Only user can update booking
        return $booking->user_id === $user->id;
    }

    public function pending(User $user, Booking $booking): bool
    {
        // Only user can set status pending
        return $booking->user_id === $user->id;
    }

    public function accept(User $user, Booking $booking): bool
    {
        // Only the worker (profile owner) can accept booking
        return $booking->profile->user_id === $user->id;
    }

    public function complete(User $user, Booking $booking): bool
    {
        // Only the worker (profile owner) can complete booking
        return $booking->profile->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        // Only the user can cancel booking
        return $booking->user_id === $user->id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        // No one can delete the booking
        return false;
    }

}