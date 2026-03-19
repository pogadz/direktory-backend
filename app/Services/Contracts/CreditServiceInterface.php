<?php

namespace App\Services\Contracts;

use App\Models\User;
use App\Models\Transaction;

interface CreditServiceInterface
{
    public function topUp(User $user, int $amount, $reference = null): Transaction;

    public function deduct(User $user, int $amount, $reference = null): Transaction;

    public function refund(User $user, int $amount, $reference = null): Transaction;
}
