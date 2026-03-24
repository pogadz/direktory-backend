<?php

namespace App\Enums;

enum CreditType: string
{
    case TOPUP = 'topup';
    case DEDUCT = 'deduct';
}
