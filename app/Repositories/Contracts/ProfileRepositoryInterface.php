<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Profile;

interface ProfileRepositoryInterface
{
    public function allByUser(int $userId): Collection;

    public function activeByUser(int $userId): Collection;

    public function findByUser(int $userId, int $profileId): ?Profile;

    public function createForUser(int $userId, array $data): Profile;

    public function updateForUser(int $userId, int $profileId, array $data): ?Profile;

    public function deleteForUser(int $userId, int $profileId): bool;

    public function findActiveProfile(int $userId, int $profileId): ?Profile;
}