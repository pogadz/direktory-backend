<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get current profile from token abilities
        $token = $user->currentAccessToken();
        $abilities = $token->abilities ?? [];

        $profileAbility = collect($abilities)->first(function ($ability) {
            return str_starts_with($ability, 'profile:');
        });

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

        // Check if profile has required role (roles passed as IDs)
        if (!empty($roles) && !$profile->hasAnyRole(array_map('intval', $roles))) {
            return response()->json([
                'message' => 'Insufficient permissions. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        // Attach profile to request for easy access in controllers
        $request->merge(['current_profile' => $profile]);

        return $next($request);
    }
}
