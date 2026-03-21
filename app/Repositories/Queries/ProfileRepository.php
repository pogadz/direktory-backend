<?php

namespace App\Repositories\Queries;

use App\Models\Profile;
use App\Models\Role;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * Get all profiles
     *
     * @param integer $userId
     * @return Collection
     */
    public function allByUser(int $userId): Collection
    {
        return Profile::where('user_id', $userId)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->get();
    }

    /**
     * Get active profiles by user
     *
     * @param integer $userId
     * @return Collection
     */
    public function activeByUser(int $userId): Collection
    {
        return Profile::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->get();
    }

    /**
     * Get a specific profile by user
     *
     * @param integer $userId
     * @param integer $profileId
     * @return Profile|null
     */
    public function findByUser(int $userId, int $profileId): ?Profile
    {
        return Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->with(['directory', 'user.userDetail', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->first();
    }

    /**
     * Create a new profile
     *
     * @param integer $userId
     * @param array $data
     * @return Profile
     */
    public function createForUser(int $userId, array $data): Profile
    {
        $data['user_id'] = $userId;
        $data['is_active'] = $data['is_active'] ?? true;

        $profile = Profile::create($data);

        // Assign worker role
        $workerRole = Role::where('name', 'worker')->first();

        if ($workerRole) {
            $profile->assignRoles([$workerRole->id]);
        }

        return $profile;
    }

    /**
     * Update profile
     *
     * @param integer $userId
     * @param integer $profileId
     * @param array $data
     * @return Profile|null
     */
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

    /**
     * Delete profile
     *
     * @param integer $userId
     * @param integer $profileId
     * @return boolean
     */
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

    /**
     * Find active profile
     *
     * @param integer $userId
     * @param integer $profileId
     * @return Profile|null
     */
    public function findActiveProfile(int $userId, int $profileId): ?Profile
    {
        return Profile::where('user_id', $userId)
            ->where('id', $profileId)
            ->where('is_active', true)
            ->with(['directory', 'user.userDetail', 'jobCategory', 'reviews.user', 'bookings' => fn ($q) => $q->where('status', 'completed')])
            ->first();
    }
}