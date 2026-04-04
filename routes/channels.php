<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Profile;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// User chat channel
Broadcast::channel('chat.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Profile chat channel
Broadcast::channel('chat.profile.{id}', function ($user, $id) {
    return $user->profiles()->where('id', (int) $id)->exists();
});