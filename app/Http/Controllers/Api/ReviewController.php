<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * @group Review
     * List all review ratings
     */
    public function index()
    {
        $reviews = Review::latest()->paginate(10);

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
        $review = Review::find($id);

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

        $review = Review::create([
            'booking_id' => $validated['booking_id'],
            'profile_id' => $validated['profile_id'],
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null
        ]);

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
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }
}