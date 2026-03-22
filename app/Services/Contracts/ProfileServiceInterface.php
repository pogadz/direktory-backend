<?php

namespace App\Services\Contracts;

use App\Models\User;

interface ProfileServiceInterface {
    public function getActiveProfileId(User $user): ?int;
    public function isUsingProfile(User $user): bool;
}