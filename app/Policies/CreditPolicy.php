<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\User;
use App\Models\Credit;

class CreditPolicy
{
    /**
     * User can view balance credit transactions
     */
    public function view(User $user)
    {
        return $user->profiles()->exists()
            ? Response::allow()
            : Response::deny('You need to create a profile first.');
    }

    /**
     * User can view balance only if they have at least one profile
     */
    public function balance(User $user)
    {
        return $user->profiles()->exists()
            ? Response::allow()
            : Response::deny('Only users that has a profile can view the balance.');
    }

    /**
     * User can top up only if they have at least one profile
     */
    public function topUp(User $user)
    {
        return $user->profiles()->exists()
            ? Response::allow()
            : Response::deny('You need to create a profile before topping up.');
    }

    /**
     * User can request refund only if they have at least one profile
     */
    public function refund(User $user)
    {
        return $user->profiles()->exists()
            ? Response::allow()
            : Response::deny('You need to create a profile before topping up.');
    }
}
