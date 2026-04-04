<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate broadcast/auth requests using a Sanctum token
 * without enforcing token abilities.
 *
 * Profile tokens are scoped to ['profile:X'] which fails Sanctum's default
 * ability check ('*'). We resolve the token manually and set the user on
 * the sanctum guard so $request->user() works inside BroadcastController.
 */
class AuthenticateBroadcastRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        $user = $accessToken->tokenable;

        // Set user on every guard that BroadcastController may call $request->user() through
        auth()->guard('sanctum')->setUser($user);
        auth()->guard('web')->setUser($user);
        auth()->setUser($user);

        // Override the request user resolver so $request->user() always returns this user
        $request->setUserResolver(fn ($guard = null) => $user);

        return $next($request);
    }
}
