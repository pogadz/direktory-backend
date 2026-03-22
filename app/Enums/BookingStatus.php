<?php

namespace App\Enums;
enum BookingStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}