<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::with('permissions')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => Permission::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|unique:roles|max:255',
            'display_name'      => 'required|string|max:255',
            'description'       => 'nullable|string',
            'permission_ids'    => 'sometimes|array',
            'permission_ids.*'  => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'           => $request->name,
            'display_name'   => $request->display_name,
            'description'    => $request->description,
            'is_system_role' => false,
        ]);

        if ($request->filled('permission_ids')) {
            $role->permissions()->attach($request->permission_ids);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('Admin/Roles/Edit', [
            'role'        => $role->load('permissions'),
            'permissions' => Permission::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system_role) {
            return back()->withErrors(['role' => 'System roles cannot be modified.']);
        }

        $request->validate([
            'name'         => 'sometimes|string|unique:roles,name,' . $role->id . '|max:255',
            'display_name' => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
        ]);

        $role->update($request->only(['name', 'display_name', 'description']));

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system_role) {
            return back()->withErrors(['role' => 'System roles cannot be deleted.']);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'permission_ids'   => 'present|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->permission_ids);

        return redirect()->route('admin.roles.edit', $role)
            ->with('success', 'Permissions updated successfully.');
    }
}
