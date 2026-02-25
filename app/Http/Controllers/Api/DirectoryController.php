<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DirectoryController extends Controller
{
    /**
     * @group Directory
     * List all directories
     */
    public function index()
    {
        $directories = Directory::latest()->get();

        return response()->json([
            'directories' => $directories,
            'total'       => $directories->count(),
        ]);
    }

    /**
     * @group Directory
     * Create a new directory
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:directories,name',
        ]);

        $directory = Directory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message'   => 'Directory created successfully',
            'directory' => $directory,
        ], 201);
    }

    /**
     * @group Directory
     * Get a specific directory
     */
    public function show($id)
    {
        $directory = Directory::findOrFail($id);

        return response()->json([
            'directory' => $directory,
        ]);
    }

    /**
     * @group Directory
     * Update a directory
     */
    public function update(Request $request, $id)
    {
        $directory = Directory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:directories,name,' . $id,
        ]);

        $directory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message'   => 'Directory updated successfully',
            'directory' => $directory->fresh(),
        ]);
    }

    /**
     * @group Directory
     * Delete a directory
     */
    public function destroy($id)
    {
        $directory = Directory::findOrFail($id);
        $directory->delete();

        return response()->json([
            'message' => 'Directory deleted successfully',
        ]);
    }
}
