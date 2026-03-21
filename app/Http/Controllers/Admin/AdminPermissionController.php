<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPermissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => Permission::with('roles')->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Permissions/Create', [
            'categories' => Permission::distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => 'required|string|unique:permissions|max:255',
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|string|max:255',
        ]);

        Permission::create([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'category'     => $request->category,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => $permission->load('roles'),
            'categories' => Permission::distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $request->validate([
            'name'         => 'sometimes|string|unique:permissions,name,' . $permission->id . '|max:255',
            'display_name' => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'sometimes|string|max:255',
        ]);

        $permission->update($request->only(['name', 'display_name', 'description', 'category']));

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
