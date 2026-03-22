<?php

namespace App\Enums;

enum TransactionType: string
{
    case BOOKING = 'booking';
    case PAYMENT = 'payment';
    case REFUND  = 'refund';
}