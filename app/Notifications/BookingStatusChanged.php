<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    public function __construct(protected Booking $booking, protected string $status) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $messages = [
            'pending'   => 'A new booking request has been made.',
            'accepted'  => 'Your booking has been accepted.',
            'completed' => 'Your booking has been marked as completed.',
            'cancelled' => 'Your booking has been cancelled.',
        ];

        return [
            'type'       => 'booking_status_changed',
            'title'      => 'Booking Update',
            'message'    => $messages[$this->status] ?? 'Your booking status has been updated.',
            'status'     => $this->status,
            'booking_id' => $this->booking->id,
        ];
    }
}
