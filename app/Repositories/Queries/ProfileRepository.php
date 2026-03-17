<?php

namespace App\Repositories\Queries;

use App\Models\Profile;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function allByUser(int $userId): Collection
    {
        return Profile::where('user_id', $userId)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->get();
    }

    public function activeByUser(int $userId): Collection
    {
        return Profile::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->get();
    }

    public function findByUser(int $userId, int $profileId): ?Profile
    {
        return Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->first();
    }

    public function createForUser(int $userId, array $data): Profile
    {
        $data['user_id'] = $userId;
        $data['is_active'] = $data['is_active'] ?? true;

        return Profile::create($data);
    }

    public function updateForUser(int $userId, int $profileId, array $data): ?Profile
    {
        $profile = Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->first();

        if (!$profile) {
            return null;
        }

        $profile->update($data);

        return $profile->fresh();
    }

    public function deleteForUser(int $userId, int $profileId): bool
    {
        $profile = Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->first();

        if (!$profile) {
            return false;
        }

        return $profile->delete();
    }

    public function findActiveProfile(int $userId, int $profileId): ?Profile
    {
        return Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->where('is_active', true)
            ->with(['directory', 'user.userDetail', 'jobCategory', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->first();
    }
}