<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\ProfileServiceInterface;

class ProfileService implements ProfileServiceInterface
{
    public function getActiveProfileId(User $user): ?int
    {
        $token = $user->currentAccessToken();

        if (!$token) return null;

        foreach ($token->abilities as $ability) {
            if (str_starts_with($ability, 'profile:')) {
                return (int) str_replace('profile:', '', $ability);
            }
        }

        return null;
    }

    public function isUsingProfile(User $user): bool
    {
        return $this->getActiveProfileId($user) !== null;
    }
}