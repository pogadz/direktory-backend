<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @hideFromAPIDocumentation
 */
class RoleController extends Controller
{
    /**
     * Get all roles
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'roles' => $roles,
            'total' => $roles->count(),
        ]);
    }

    /**
     * Create a new role (Admin only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_system_role' => false,
        ]);

        if ($request->has('permission_ids')) {
            $role->permissions()->attach($request->permission_ids);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions'),
        ], 201);
    }

    /**
     * Get a specific role
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'role' => $role,
        ]);
    }

    /**
     * Update a role (Admin only)
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system_role) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be modified.'],
            ]);
        }

        $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id . '|max:255',
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $role->update($request->only(['name', 'display_name', 'description']));

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role->fresh()->load('permissions'),
        ]);
    }

    /**
     * Delete a role (Admin only)
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system_role) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be deleted.'],
            ]);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Assign permissions to a role (Admin only)
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->givePermissions($request->permission_ids);

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Remove permissions from a role (Admin only)
     */
    public function revokePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->revokePermissions($request->permission_ids);

        return response()->json([
            'message' => 'Permissions revoked successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Sync permissions for a role (Admin only)
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->permission_ids);

        return response()->json([
            'message' => 'Permissions synced successfully',
            'role' => $role->load('permissions'),
        ]);
    }
}
