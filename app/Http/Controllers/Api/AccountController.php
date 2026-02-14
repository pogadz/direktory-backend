<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Get all accounts for the authenticated user
     */
    public function index(Request $request)
    {
        $accounts = $request->user()->accounts()->get();

        return response()->json([
            'accounts' => $accounts,
            'total' => $accounts->count(),
        ]);
    }

    /**
     * Create a new account for the authenticated user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|string',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $account = $request->user()->accounts()->create([
            'name' => $request->name,
            'avatar' => $request->avatar,
            'bio' => $request->bio,
            'address' => $request->address,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account,
        ], 201);
    }

    /**
     * Get a specific account
     */
    public function show(Request $request, $id)
    {
        $account = $request->user()->accounts()->findOrFail($id);

        return response()->json([
            'account' => $account,
        ]);
    }

    /**
     * Update an account
     */
    public function update(Request $request, $id)
    {
        $account = $request->user()->accounts()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'nullable|string',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $account->update($request->only(['name', 'avatar', 'bio', 'address', 'is_active']));

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account->fresh(),
        ]);
    }

    /**
     * Delete an account
     */
    public function destroy(Request $request, $id)
    {
        $account = $request->user()->accounts()->findOrFail($id);
        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    /**
     * Switch to a different account (stores account_id in token abilities)
     */
    public function switch(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $account = $request->user()->accounts()->where('id', $request->account_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token with account context
        $token = $request->user()->createToken(
            'auth_token',
            ['account:' . $account->id],
            now()->addMinutes(config('sanctum.expiration', 60))
        )->plainTextToken;

        return response()->json([
            'message' => 'Switched to account successfully',
            'account' => $account,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 60) * 60,
        ]);
    }

    /**
     * Get the current active account from token
     */
    public function current(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities;

        // Extract account ID from abilities
        $accountAbility = collect($abilities)->first(function ($ability) {
            return str_starts_with($ability, 'account:');
        });

        if ($accountAbility) {
            $accountId = str_replace('account:', '', $accountAbility);
            $account = $request->user()->accounts()->find($accountId);

            if ($account) {
                return response()->json([
                    'account' => $account,
                ]);
            }
        }

        return response()->json([
            'account' => null,
            'message' => 'No account selected. Use /accounts/switch to select an account.',
        ]);
    }

    /**
     * Get active accounts only
     */
    public function active(Request $request)
    {
        $accounts = $request->user()->activeAccounts()->get();

        return response()->json([
            'accounts' => $accounts,
            'total' => $accounts->count(),
        ]);
    }
}
