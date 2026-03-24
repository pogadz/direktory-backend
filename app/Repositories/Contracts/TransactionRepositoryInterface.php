<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Transaction;

interface TransactionRepositoryInterface
{
    public function getAllByUser(int $userId): Collection;

    public function findById(int $id): ?Transaction;

    public function getByProfile(int $profileId): Collection;
}