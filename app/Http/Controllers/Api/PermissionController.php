<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Get all permissions
     */
    public function index(Request $request)
    {
        $permissions = Permission::with('roles')->get();

        return response()->json([
            'permissions' => $permissions,
            'total' => $permissions->count(),
        ]);
    }

    /**
     * Get permissions grouped by category
     */
    public function indexByCategory(Request $request)
    {
        $permissions = Permission::all()->groupBy('category');

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Create a new permission (Admin only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category ?? 'general',
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'permission' => $permission,
        ], 201);
    }

    /**
     * Get a specific permission
     */
    public function show($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);

        return response()->json([
            'permission' => $permission,
        ]);
    }

    /**
     * Update a permission (Admin only)
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|unique:permissions,name,' . $id . '|max:255',
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:255',
        ]);

        $permission->update($request->only(['name', 'display_name', 'description', 'category']));

        return response()->json([
            'message' => 'Permission updated successfully',
            'permission' => $permission->fresh(),
        ]);
    }

    /**
     * Delete a permission (Admin only)
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully',
        ]);
    }
}
