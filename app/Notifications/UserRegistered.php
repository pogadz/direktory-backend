<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserRegistered extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'user_registered',
            'title'   => 'Welcome to Direktory!',
            'message' => "Hi {$notifiable->firstname}, your account has been created successfully.",
        ];
    }
}
