<?php

namespace App\Services;

use App\Models\User;
use App\Models\Credit;
use App\Models\Transaction;
use App\Services\Contracts\CreditServiceInterface;
use Illuminate\Support\Facades\DB;

class CreditService implements CreditServiceInterface
{
    public function topUp(User $user, int $amount, $reference = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference) {

            $transaction = $user->transactions()->create([
                'type' => Transaction::TYPE_PAYMENT,
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => $amount,
                'action_type' => Credit::ACTION_TOPUP,
                'transaction_type' => Transaction::TYPE_PAYMENT,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }

    public function deduct(User $user, int $amount, $reference = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference) {

            $balance = $user->credits()
                ->lockForUpdate()
                ->pluck('amount')
                ->sum();

            if ($balance < $amount) {
                throw new \Exception('Insufficient credits');
            }

            $transaction = $user->transactions()->create([
                'type' => Transaction::TYPE_BOOKING,
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => -$amount,
                'action_type' => Credit::ACTION_DEDUCT,
                'transaction_type' => Transaction::TYPE_BOOKING,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }

    public function refund(User $user, int $amount, $reference = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference) {

            $transaction = $user->transactions()->create([
                'type' => Transaction::TYPE_REFUND,
                'amount' => $amount,
                'status' => Transaction::STATUS_COMPLETED,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => $amount,
                'action_type' => Credit::ACTION_REFUND,
                'transaction_type' => Transaction::TYPE_REFUND,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }
}
