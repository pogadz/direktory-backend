<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountRoleController extends Controller
{
    /**
     * Get all roles assigned to an account
     */
    public function index(Request $request, $accountId)
    {
        $account = $request->user()->accounts()->findOrFail($accountId);
        $roles = $account->roles()->with('permissions')->get();

        return response()->json([
            'account' => $account,
            'roles' => $roles,
            'total' => $roles->count(),
        ]);
    }

    /**
     * Assign roles to an account
     */
    public function assign(Request $request, $accountId)
    {
        $account = $request->user()->accounts()->findOrFail($accountId);

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $account->assignRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles assigned successfully',
            'account' => $account->load('roles.permissions'),
        ]);
    }

    /**
     * Remove roles from an account
     */
    public function revoke(Request $request, $accountId)
    {
        $account = $request->user()->accounts()->findOrFail($accountId);

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $account->removeRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles removed successfully',
            'account' => $account->load('roles.permissions'),
        ]);
    }

    /**
     * Sync roles for an account (replace all roles)
     */
    public function sync(Request $request, $accountId)
    {
        $account = $request->user()->accounts()->findOrFail($accountId);

        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $account->syncRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles synced successfully',
            'account' => $account->load('roles.permissions'),
        ]);
    }

    /**
     * Get all permissions for an account (through roles)
     */
    public function permissions(Request $request, $accountId)
    {
        $account = $request->user()->accounts()->findOrFail($accountId);
        $permissions = $account->getAllPermissions();

        return response()->json([
            'account' => $account,
            'permissions' => $permissions,
            'total' => $permissions->count(),
        ]);
    }
}
