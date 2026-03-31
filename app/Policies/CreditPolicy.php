<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Credit;

class CreditPolicy
{
    /**
     * User can view balance credit transactions
     */
    public function view(User $user): bool
    {
        return $user->profiles()->exists();
    }

    /**
     * User can view balance only if they have at least one profile
     */
    public function balance(User $user): bool
    {
        return $user->profiles()->exists();
    }

    /**
     * User can top up only if they have at least one profile
     */
    public function topUp(User $user): bool
    {
        return $user->profiles()->exists();
    }

    /**
     * User can request refund only if they have at least one profile
     */
    public function refund(User $user): bool
    {
        return $user->profiles()->exists();
    }
}
