<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

/**
 * @hideFromAPIDocumentation
 */
class ProfileRoleController extends Controller
{
    /**
     * Get all roles assigned to a profile
     */
    public function index(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $roles = $profile->roles()->with('permissions')->get();

        return response()->json([
            'profile' => $profile,
            'roles' => $roles,
            'total' => $roles->count(),
        ]);
    }

    /**
     * Assign roles to a profile
     */
    public function assign(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $profile->assignRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles assigned successfully',
            'profile' => $profile->load('roles.permissions'),
        ]);
    }

    /**
     * Remove roles from a profile
     */
    public function revoke(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $profile->removeRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles removed successfully',
            'profile' => $profile->load('roles.permissions'),
        ]);
    }

    /**
     * Sync roles for a profile (replace all roles)
     */
    public function sync(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);

        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $profile->syncRoles($request->role_ids);

        return response()->json([
            'message' => 'Roles synced successfully',
            'profile' => $profile->load('roles.permissions'),
        ]);
    }

    /**
     * Get all permissions for a profile (through roles)
     */
    public function permissions(Request $request, $profileId)
    {
        $profile = $request->user()->profiles()->findOrFail($profileId);
        $permissions = $profile->getAllPermissions();

        return response()->json([
            'profile' => $profile,
            'permissions' => $permissions,
            'total' => $permissions->count(),
        ]);
    }
}
