<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function allWithDetails(): Collection;

    public function findByIdWithDetails(int $id): ?Model;

    public function updateUserAndDetails(int $userId, array $userData, array $detailData): ?Model;

    public function deleteUser(int $userId): bool;
}