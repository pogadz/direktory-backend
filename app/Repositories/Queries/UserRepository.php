<?php

namespace App\Repositories\Queries;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get all users with their details
     *
     * @return Collection
     */
    public function allWithDetails(): Collection
    {
        return User::with('user_detail')->get();
    }

    /**
     * Get specific user with their details
     *
     * @param integer $id
     * @return User|null
     */
    public function findByIdWithDetails(int $id): ?Model
    {
        return User::with('user_detail')->find($id);
    }

    /**
     * Create a new user with their details
     *
     * @param array $userData
     * @param array $detailData
     * @return User
     */
    public function updateUserAndDetails(int $userId, array $userData, array $detailData): ?Model
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $user->update($userData);

        $user->user_detail()->updateOrCreate(
            ['user_id' => $user->id],
            $detailData
        );

        return $user->fresh('user_detail');
    }

    /**
     * Delete a user
     *
     * @param integer $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // delete access tokens if using Laravel Sanctum
        $user->currentAccessToken()?->delete();

        return $user->delete();
    }
}