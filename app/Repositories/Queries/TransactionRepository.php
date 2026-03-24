<?php

namespace App\Repositories\Queries;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getAllByUser(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->with('profile')
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with('profile')->find($id);
    }

    public function getByProfile(int $profileId): Collection
    {
        return Transaction::where('profile_id', $profileId)
            ->latest()
            ->get();
    }
}