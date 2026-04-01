<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Profile;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// User's personal chat channel
Broadcast::channel('chat.user.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

// Profile chat channel — authenticated user must own the active profile
Broadcast::channel('chat.profile.{profileId}', function ($user, int $profileId) {
    return $user->profiles()->where('id', $profileId)->exists();
});
