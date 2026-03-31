<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Booking;
use App\Models\User;
use App\Services\ProfileService;

class BookingPolicy
{
    /**
     * All can view booking
     */
    public function view(User $user, Booking $booking): bool
    {
        return true;
    }

    /**
     * Only users can create booking
     */
    public function create(User $user)
    {
        return !app(ProfileService::class)->isUsingProfile($user)
            ? Response::allow()
            : Response::deny('Only users can create booking');
    }

    /**
     * Only users can update booking
     */
    public function update(User $user, Booking $booking)
    {
        return $booking->user_id === $user->id
            ? Response::allow()
            : Response::deny('Only users can update booking');
    }

    /**
     * Only users can set status pending
     */
    public function pending(User $user, Booking $booking)
    {
        return $booking->user_id === $user->id
            ? Response::allow()
            : Response::deny('Only users can set status pending');
    }

    /**
     * Only worker can set status accepted
     */
    public function accepted(User $user, Booking $booking)
    {
        $activeProfileId = app(ProfileService::class)->getActiveProfileId($user);

        return $activeProfileId !== null && (int) $activeProfileId === (int) $booking->profile_id
            ? Response::allow()
            : Response::deny('Only worker can accept booking');
    }

    /**
     * Only worker can set status completed
     */
    public function completed(User $user, Booking $booking)
    {
        $activeProfileId = app(ProfileService::class)->getActiveProfileId($user);

        return $activeProfileId !== null && (int) $activeProfileId === (int) $booking->profile_id
            ? Response::allow()
            : Response::deny('Only worker can complete booking');
    }

    /**
     * Only users can cancel booking
     */
    public function cancelled(User $user, Booking $booking)
    {
        return $booking->user_id === $user->id
            ? Response::allow()
            : Response::deny('Only users can cancel booking');
    }

    /**
     * No one can delete booking
     */
    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }
}
