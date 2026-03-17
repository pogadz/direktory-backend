<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProfileResource;
use App\Repositories\Contracts\ProfileRepositoryInterface;

class ProfileController extends Controller
{
    protected $profiles;

    public function __construct(ProfileRepositoryInterface $profiles)
    {
        $this->profiles = $profiles;
    }

    /**
     * @group Profile
     * Get all profiles for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $profiles = $this->profiles->allByUser($user->id);

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
            'name'            => 'required|string|max:255',
            'directory_id'    => 'required|exists:directories,id',
            'job_category_id' => 'required|exists:job_categories,id',
            'avatar'          => 'nullable|string',
            'bio'             => 'nullable|string',
            'address'         => 'nullable|string',
            'hourly_rate'     => 'nullable|string',
            'response_time'   => 'nullable|string',
        ]);

        $profile = $this->profiles->createForUser($request->user()->id, $request->all());

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
        $profile = $this->profiles->findByUser($request->user()->id, $id);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

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
        $request->validate([
            'name'            => 'required|string|max:255',
            'directory_id'    => 'required|exists:directories,id',
            'job_category_id' => 'required|exists:job_categories,id',
            'avatar'          => 'nullable|string',
            'bio'             => 'nullable|string',
            'address'         => 'nullable|string',
            'hourly_rate'     => 'nullable|string',
            'response_time'   => 'nullable|string',
            'is_active'       => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'name', 'directory_id', 'job_category_id', 'avatar', 'bio', 'address',
            'hourly_rate', 'response_time', 'is_active'
        ]);

        $profile = $this->profiles->updateForUser($request->user()->id, $id, $data);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile,
        ]);
    }

    /**
     * @group Profile
     * Delete a profile
     */
    public function destroy(Request $request, $id)
    {
        $deleted = $this->profiles->deleteForUser($request->user()->id, $id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

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

        $profile = $this->profiles->findActiveProfile($request->user()->id, $request->profile_id);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found or inactive',
            ], 404);
        }

        $request->user()->currentAccessToken()->delete();

        $token = $request->user()->createToken(
            'auth_token',
            ['profile:' . $profile->id],
            now()->addMinutes(config('sanctum.expiration', 60))
        )->plainTextToken;

        return response()->json([
            'message' => 'Switched to profile successfully',
            'profile' => new ProfileResource($profile),
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
        $abilities = $token?->abilities ?? [];

        $profileAbility = collect($abilities)->first(fn($ability) => str_starts_with($ability, 'profile:'));

        if ($profileAbility) {
            $profileId = str_replace('profile:', '', $profileAbility);
            $profile = $this->profiles->findByUser($request->user()->id, (int)$profileId);

            if ($profile) {
                return response()->json(['profile' => new ProfileResource($profile)]);
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
        $profiles = $this->profiles->activeByUser($request->user()->id);

        return response()->json([
            'profiles' => $profiles,
            'total' => $profiles->count(),
        ]);
    }
}
