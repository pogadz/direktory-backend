<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @group User
     * List all users with their details
     */
    public function index()
    {
        $users = User::with('user_detail')->get();

        return response()->json([
            'users' => $users,
            'total' => $users->count(),
        ]);
    }

    /**
     * @group User
     * Get a specific user with their details
     */
    public function show($id)
    {
        $user = User::with('user_detail')->findOrFail($id);

        return response()->json(new UserResource($user));
    }

    /**
     * @group User
     * Update authenticated user
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'firstname' => 'sometimes|string|max:255',
            'lastname'  => 'sometimes|string|max:255',
            'avatar'       => 'nullable|string',
            'profession'   => 'nullable|string|max:255',
            'status_emoji' => 'nullable|string|max:10',
            'status_text'  => 'nullable|string|max:255',
            'location'     => 'nullable|string|max:255',
            'responseTime' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['firstname', 'lastname', 'email']));

        $user->user_detail()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['avatar', 'profession', 'status_emoji', 'status_text', 'location', 'responseTime'])
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->fresh('user_detail'),
        ]);
    }

    /**
     * @group User
     * Delete the authenticated user's account
     */
    // public function destroy(Request $request)
    // {
    //     $user = $request->user();

    //     $user->currentAccessToken()->delete();
    //     $user->delete();

    //     return response()->json([
    //         'message' => 'User deleted successfully',
    //     ]);
    // }
}
