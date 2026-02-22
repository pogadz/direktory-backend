<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @group Profile
     * Get all profiles for the authenticated user
     */
    public function index(Request $request)
    {
        $profiles = $request->user()->profiles()->get();

        return response()->json([
            'profiles' => $profiles,
            'total' => $profiles->count(),
        ]);
    }

    /**
     * @group Profile
     * Create a new profile for the authenticated user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'job_category_id' => 'required|integer',
            'avatar' => 'nullable|string',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $profile = $request->user()->profiles()->create([
            'name' => $request->name,
            'avatar' => $request->avatar,
            'bio' => $request->bio,
            'address' => $request->address,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile,
        ], 201);
    }

    /**
     * @group Profile
     * Get a specific profile
     */
    public function show(Request $request, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($id);

        return response()->json([
            'profile' => $profile,
        ]);
    }

    /**
     * @group Profile
     * Update a profile
     */
    public function update(Request $request, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'job_category_id' => 'required|integer',
            'avatar' => 'nullable|string',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $profile->update($request->only(['name', 'avatar', 'bio', 'address', 'is_active']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * @group Profile
     * Delete a profile
     */
    public function destroy(Request $request, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($id);
        $profile->delete();

        return response()->json([
            'message' => 'Profile deleted successfully',
        ]);
    }

    /**
     * @group Profile
     * Switch to a different profile
     */
    public function switch(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $profile = $request->user()->profiles()->where('id', $request->profile_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token with profile context
        $token = $request->user()->createToken(
            'auth_token',
            ['profile:' . $profile->id],
            now()->addMinutes(config('sanctum.expiration', 60))
        )->plainTextToken;

        return response()->json([
            'message' => 'Switched to profile successfully',
            'profile' => $profile,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 60) * 60,
        ]);
    }

    /**
     * @group Profile
     * Get the current active profile from token
     */
    public function current(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities;

        // Extract profile ID from abilities
        $profileAbility = collect($abilities)->first(function ($ability) {
            return str_starts_with($ability, 'profile:');
        });

        if ($profileAbility) {
            $profileId = str_replace('profile:', '', $profileAbility);
            $profile = $request->user()->profiles()->find($profileId);

            if ($profile) {
                return response()->json([
                    'profile' => $profile,
                ]);
            }
        }

        return response()->json([
            'profile' => null,
            'message' => 'No profile selected. Use /profiles/switch to select a profile.',
        ]);
    }

    /**
     * @group Profile
     * Get active profiles only
     */
    public function active(Request $request)
    {
        $profiles = $request->user()->activeProfiles()->get();

        return response()->json([
            'profiles' => $profiles,
            'total' => $profiles->count(),
        ]);
    }
}
