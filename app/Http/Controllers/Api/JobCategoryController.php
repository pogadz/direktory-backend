<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\JobCategoryRepositoryInterface;

class JobCategoryController extends Controller
{
    protected $categories;

    public function __construct(JobCategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @group Job Category
     * Get all job categories
     * @unauthenticated
     */
    public function index()
    {
        $categories = $this->categories->allOrderedByName();

        return response()->json([
            'job_categories' => $categories,
            'total' => $categories->count(),
        ]);
    }

    /**
     * @group Job Category
     * Get a specific job category
     * @unauthenticated
     */
    public function show($id)
    {
        $category = $this->categories->findById($id);

        if (!$category) {
            return response()->json([
                'message' => 'Job category not found',
            ], 404);
        }

        return response()->json([
            'job_category' => $category,
        ]);
    }

    /**
     * @group Job Category
     * Create a new job category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name',
        ]);

        $category = $this->categories->create($validated);

        return response()->json([
            'message' => 'Job category created successfully',
            'job_category' => $category,
        ], 201);
    }

    /**
     * @group Job Category
     * Update a job category
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,' . $id,
        ]);

        $category = $this->categories->update($id, $validated);

        if (!$category) {
            return response()->json([
                'message' => 'Job category not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Job category updated successfully',
            'job_category' => $category,
        ]);
    }

    /**
     * @group Job Category
     * Delete a job category
     */
    public function destroy($id)
    {
        $deleted = $this->categories->delete($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Job category not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Job category deleted successfully',
        ]);
    }
}
