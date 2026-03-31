<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Enums\CreditType;
use App\Services\Contracts\CreditServiceInterface;
use Illuminate\Support\Facades\DB;

class CreditService implements CreditServiceInterface
{
    public function topUp(User $user, int $amount, $reference = null, ?int $profileId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference, $profileId) {

            $transaction = $user->transactions()->create([
                'profile_id' => $profileId,
                'type' => TransactionType::PAYMENT,
                'amount' => $amount,
                'status' => TransactionStatus::SUCCESS,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => $amount,
                'action_type' => CreditType::TOPUP,
                'transaction_type' => TransactionType::PAYMENT,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }

    public function applyTopUp(User $user, int $amount, Transaction $transaction): void
    {
        DB::transaction(function () use ($user, $amount, $transaction) {

            // ✅ Idempotency check (prevents double crediting)
            if ($transaction->credits()->exists()) {
                return;
            }

            // ✅ Create credit entry
            $user->credits()->create([
                'amount' => $amount,
                'action_type' => CreditType::TOPUP,
                'transaction_type' => TransactionType::PAYMENT,
                'transaction_id' => $transaction->id,
            ]);
        });
    }

    public function deduct(User $user, int $amount, $reference = null, ?int $profileId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference, $profileId) {

            $balance = $user->credits()
                ->lockForUpdate()
                ->pluck('amount')
                ->sum();

            if ($balance < $amount) {
                throw new \Exception('Insufficient credits');
            }

            $transaction = $user->transactions()->create([
                'profile_id' => $profileId,
                'type' => TransactionType::BOOKING,
                'amount' => $amount,
                'status' => TransactionStatus::SUCCESS,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => -$amount,
                'action_type' => CreditType::DEDUCT,
                'transaction_type' => TransactionType::BOOKING,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }

    public function refund(User $user, int $amount, $reference = null, ?int $profileId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $reference, $profileId) {

            $transaction = $user->transactions()->create([
                'profile_id' => $profileId,
                'type' => TransactionType::REFUND,
                'amount' => $amount,
                'status' => TransactionStatus::SUCCESS,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            $user->credits()->create([
                'amount' => $amount,
                'action_type' => TransactionType::REFUND,
                'transaction_type' => TransactionType::REFUND,
                'transaction_id' => $transaction->id,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);

            return $transaction;
        });
    }
}
