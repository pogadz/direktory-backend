<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountRole
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

        // Get current account from token abilities
        $token = $user->currentAccessToken();
        $abilities = $token->abilities ?? [];

        $accountAbility = collect($abilities)->first(function ($ability) {
            return str_starts_with($ability, 'account:');
        });

        if (!$accountAbility) {
            return response()->json([
                'message' => 'No account selected. Please switch to an account first.',
            ], 403);
        }

        $accountId = str_replace('account:', '', $accountAbility);
        $account = $user->accounts()->find($accountId);

        if (!$account) {
            return response()->json([
                'message' => 'Account not found or access denied.',
            ], 403);
        }

        if (!$account->is_active) {
            return response()->json([
                'message' => 'Account is inactive.',
            ], 403);
        }

        // Check if account has required role
        if (!empty($roles) && !in_array($account->role, $roles)) {
            return response()->json([
                'message' => 'Insufficient permissions. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        // Attach account to request for easy access in controllers
        $request->merge(['current_account' => $account]);

        return $next($request);
    }
}
