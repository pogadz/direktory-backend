<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Services\ProfileService;

class ReviewPolicy
{
    /**
     * Only allow review creation if user is NOT acting as a profile
     */
    public function create(User $user): Response
    {
        return !app(ProfileService::class)->isUsingProfile($user)
            ? Response::allow()
            : Response::deny('You cannot leave a review while using a profile.');
    }

    /**
     * Only allow update if user owns the review AND not acting as profile
     */
    public function update(User $user, $review): Response
    {
        if (app(ProfileService::class)->isUsingProfile($user)) {
            return Response::deny('You cannot update a review while using a profile.');
        }

        return $review->user_id === $user->id
            ? Response::allow()
            : Response::deny('You are not allowed to update this review.');
    }
}
