<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNoProfileSelected
{
    /**
     * Only allow requests from users who have NOT switched into a profile.
     * This enforces that bookings can only be made as a plain user, not as a worker profile.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $abilities = $request->user()?->currentAccessToken()?->abilities ?? [];

        $hasProfile = collect($abilities)->contains(fn ($a) => str_starts_with($a, 'profile:'));

        if ($hasProfile) {
            return response()->json([
                'message' => 'This action must be performed as a user, not as a profile.',
            ], 403);
        }

        return $next($request);
    }
}
