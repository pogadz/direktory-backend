<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * @group User
 */
class UserController extends Controller
{
    protected $users;

    public function __construct(UserRepositoryInterface $users)
    {
        $this->users = $users;
    }

    /**
     * List all users with their details
     */
    public function index()
    {
        $users = $this->users->allWithDetails();

        return response()->json([
            'users' => $users,
            'total' => $users->count(),
        ]);
    }

    /**
     * Get a specific user with their details
     */
    public function show($id)
    {
        $user = $this->users->findByIdWithDetails($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json(new UserResource($user));
    }

    /**
     * Update authenticated user
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'phone'     => 'nullable|string|max:255',
            'avatar'       => 'nullable|string',
            'profession'   => 'nullable|string|max:255',
            'status_emoji' => 'nullable|string|max:10',
            'status_text'  => 'nullable|string|max:255',
            'location'     => 'nullable|string|max:255',
        ]);

        $userData = $request->only(['firstname', 'lastname', 'phone', 'email']);
        $detailData = $request->only(['avatar', 'profession', 'status_emoji', 'status_text', 'location']);

        $updatedUser = $this->users->updateUserAndDetails($user->id, $userData, $detailData);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $updatedUser,
        ]);
    }

    /**
     * Delete the authenticated user's account
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $this->users->deleteUser($user->id);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
