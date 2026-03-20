<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CreditToppedUp extends Notification
{
    public function __construct(protected int $amount, protected int $balance) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'credit_topped_up',
            'title'   => 'Credits Added',
            'message' => "{$this->amount} credits have been added to your account. New balance: {$this->balance}.",
            'amount'  => $this->amount,
            'balance' => $this->balance,
        ];
    }
}
