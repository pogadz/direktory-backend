<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $token = $user->currentAccessToken();

            // Check if token has expired
            if ($token && $token->expires_at && $token->expires_at->isPast()) {
                return response()->json([
                    'message' => 'Token has expired. Please refresh your token.',
                    'error' => 'token_expired'
                ], 401);
            }
        }

        return $next($request);
    }
}
