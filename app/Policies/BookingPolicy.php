<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use App\Services\ProfileService;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Only users not currently acting as a profile can create bookings
        return !app(ProfileService::class)->isUsingProfile($user);
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

    public function accepted(User $user, Booking $booking): bool
    {
        // Only the worker (profile owner) can accept booking
        $activeProfileId = app(ProfileService::class)->getActiveProfileId($user);

        return $activeProfileId !== null && (int) $activeProfileId === (int) $booking->profile_id;
    }

    public function completed(User $user, Booking $booking): bool
    {
        // Only the worker (profile owner) can complete booking
        $activeProfileId = app(ProfileService::class)->getActiveProfileId($user);

        return $activeProfileId !== null && (int) $activeProfileId === (int) $booking->profile_id;
    }

    public function cancelled(User $user, Booking $booking): bool
    {
        // Only the user can cancel booking
        return $booking->user_id === $user->id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

}