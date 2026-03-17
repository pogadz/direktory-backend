<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewController extends Controller
{
    protected $reviews;

    public function __construct(ReviewRepositoryInterface $reviews)
    {
        $this->reviews = $reviews;
    }

    /**
     * @group Review
     * List all review ratings
     */
    public function index()
    {
        $reviews = $this->reviews->paginateLatest(10);

        return response()->json([
            'status' => true,
            'data' => $reviews
        ]);
    }

    /**
     * @group Review
     * Get a specific review rating
     */
    public function show($id)
    {
        $review = $this->reviews->findById($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $review
        ]);
    }

    /**
     * @group Review
     * Create a new review rating
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'profile_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $validated['user_id'] = auth()->id();

        $review = $this->reviews->create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * @group Review
     * Update a review rating
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review = $this->reviews->update($id, $validated);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }
}
