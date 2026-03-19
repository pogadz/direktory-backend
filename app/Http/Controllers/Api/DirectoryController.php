<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\DirectoryRepositoryInterface;

/**
 * @group Directory
 */
class DirectoryController extends Controller
{
    protected $directories;

    public function __construct(DirectoryRepositoryInterface $directories)
    {
        $this->directories = $directories;
    }

    /**
     * List all directories
     */
    public function index()
    {
        $directories = $this->directories->all();

        return response()->json([
            'directories' => $directories,
            'total'       => $directories->count(),
        ]);
    }

    /**
     * Create a new directory
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:directories,name',
        ]);

        $directory = $this->directories->create($request->only('name'));

        return response()->json([
            'message'   => 'Directory created successfully',
            'directory' => $directory,
        ], 201);
    }

    /**
     * Get a specific directory
     */
    public function show($id)
    {
        $directory = $this->directories->find($id);

        if (!$directory) {
            return response()->json([
                'message' => 'Directory not found',
            ], 404);
        }

        return response()->json([
            'directory' => $directory,
        ]);
    }

    /**
     * Update a directory
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:directories,name,' . $id,
        ]);

        $directory = $this->directories->update($id, $request->only('name'));

        if (!$directory) {
            return response()->json([
                'message' => 'Directory not found',
            ], 404);
        }

        return response()->json([
            'message'   => 'Directory updated successfully',
            'directory' => $directory,
        ]);
    }

    /**
     * Delete a directory
     */
    public function destroy($id)
    {
        $deleted = $this->directories->delete($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Directory not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Directory deleted successfully',
        ]);
    }
}
