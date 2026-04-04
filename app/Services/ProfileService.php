<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\ProfileServiceInterface;

class ProfileService implements ProfileServiceInterface
{
    /**
     * Get Active Profile id
     *
     * @param User $user
     * @return integer|null
     */
    public function getActiveProfileId(User $user): ?int
    {
        $token = $user->currentAccessToken();

        if (!$token) return null;

        // Return token-based active profile
        foreach ($token->abilities as $ability) {
            if (str_starts_with($ability, 'profile:')) {
                return (int) str_replace('profile:', '', $ability);
            }
        }

        // Return fallback user’s selected/default profile
        if ($user->active_profile_id) {
            return $user->active_profile_id;
        }

        // Otherwise return the latest profile
        return $user->profiles()->latest()->value('id');
    }

    /**
     * Check if it's using profile
     *
     * @param User $user
     * @return boolean
     */
    public function isUsingProfile(User $user): bool
    {
        return $this->getActiveProfileId($user) !== null;
    }
}