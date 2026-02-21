<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request to check if profile has required permissions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $token = $user->currentAccessToken();
        $abilities = $token->abilities ?? [];

        // Admin path: token with wildcard ability ('*') bypasses permission checks.
        // Only the admin user receives a wildcard token (issued on login).
        if (in_array('*', $abilities)) {
            return $next($request);
        }

        // Standard path: token must have a profile: ability (set via /profiles/switch)
        $profileAbility = collect($abilities)->first(fn($ability) => str_starts_with($ability, 'profile:'));

        if (!$profileAbility) {
            return response()->json([
                'message' => 'No profile selected. Please switch to a profile first.',
            ], 403);
        }

        $profileId = str_replace('profile:', '', $profileAbility);
        $profile = $user->profiles()->find($profileId);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found or access denied.',
            ], 403);
        }

        if (!$profile->is_active) {
            return response()->json([
                'message' => 'Profile is inactive.',
            ], 403);
        }

        // Check if profile has required permissions
        if (!empty($permissions)) {
            if (!$profile->hasAnyPermission($permissions)) {
                return response()->json([
                    'message' => 'Insufficient permissions. Required: ' . implode(' or ', $permissions),
                ], 403);
            }
        }

        $request->merge(['current_profile' => $profile]);

        return $next($request);
    }
}
