<?php

namespace App\Repositories\Queries;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function allWithDetails(): Collection
    {
        return User::with('user_detail')->get();
    }

    public function findByIdWithDetails(int $id)
    {
        return User::with('user_detail')->find($id);
    }

    public function updateUserAndDetails(int $userId, array $userData, array $detailData)
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