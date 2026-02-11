<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request to check if account has required permissions.
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

        // Check if account has required permissions
        if (!empty($permissions)) {
            $hasPermission = $account->hasAnyPermission($permissions);

            if (!$hasPermission) {
                return response()->json([
                    'message' => 'Insufficient permissions. Required: ' . implode(' or ', $permissions),
                ], 403);
            }
        }

        // Attach account to request for easy access in controllers
        $request->merge(['current_account' => $account]);

        return $next($request);
    }
}
