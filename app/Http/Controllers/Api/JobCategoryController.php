<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobCategoryController extends Controller
{
    /**
     * @group Job Categories
     * Get all job categories
     * @unauthenticated
     */
    public function index()
    {
        $categories = JobCategory::orderBy('name')->get();

        return response()->json([
            'job_categories' => $categories,
            'total' => $categories->count(),
        ]);
    }

    /**
     * @group Job Categories
     * Get a specific job category
     * @unauthenticated
     */
    public function show($id)
    {
        $category = JobCategory::findOrFail($id);

        return response()->json([
            'job_category' => $category,
        ]);
    }

    /**
     * @group Job Categories
     * Create a new job category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name',
        ]);

        $category = JobCategory::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Job category created successfully',
            'job_category' => $category,
        ], 201);
    }

    /**
     * @group Job Categories
     * Update a job category
     */
    public function update(Request $request, $id)
    {
        $category = JobCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,' . $id,
        ]);

        $category->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Job category updated successfully',
            'job_category' => $category->fresh(),
        ]);
    }

    /**
     * @group Job Categories
     * Delete a job category
     */
    public function destroy($id)
    {
        $category = JobCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Job category deleted successfully',
        ]);
    }
}
