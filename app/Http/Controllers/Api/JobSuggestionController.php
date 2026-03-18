<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\JobSuggestionRepositoryInterface;

class JobSuggestionController extends Controller
{
    protected JobSuggestionRepositoryInterface $repository;

    public function __construct(JobSuggestionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @group JobSuggestion
     * List all job suggestions
     */
    public function index()
    {
        $jobSuggestions = $this->repository->allPaginated(10);

        return response()->json([
            'status' => true,
            'data' => $jobSuggestions
        ]);
    }

    /**
     * @group JobSuggestion
     * Create job suggestion
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_title' => 'required|string',
            'description' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['upvotes'] = 0;
        $validated['status'] = 'pending';

        $jobSuggestion = $this->repository->create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Job suggestion created successfully',
            'data' => $jobSuggestion
        ], 201);
    }

    /**
     * @group JobSuggestion
     * Update job suggestion
     *
     * @urlParam id integer required The ID of the job suggestion. Example: "".
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'job_title' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,approved,rejected'
        ]);

        $jobSuggestion = $this->repository->update($id, $validated);

        if (!$jobSuggestion) {
            return response()->json([
                'status' => false,
                'message' => 'Job suggestion not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Job suggestion updated successfully',
            'data' => $jobSuggestion
        ]);
    }

    /**
     * @group JobSuggestion
     * Toggle upvote
     *
     * @urlParam job_suggestion_id integer required The ID of the job suggestion. Example: "".
     */
    public function toggleUpvote(Request $request, $job_suggestion_id)
    {
        $result = $this->repository->toggleUpvote($job_suggestion_id, $request->user()->id);

        if (isset($result['error'])) {
            return response()->json([
                'status' => false,
                'message' => $result['error']
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Upvote status updated successfully',
            'upvoted' => $result['upvoted'],
            'data' => $result['jobSuggestion']
        ]);
    }

    /**
     * @group JobSuggestion
     * Delete job suggestion
     *
     * @urlParam id integer required The ID of the job suggestion. Example: "".
     */
    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Job suggestion not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Job suggestion deleted successfully'
        ]);
    }
}