<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;

class RatingController extends Controller
{
    /**
     * @group Rating
     * List all ratings
     */
    public function index()
    {
        $ratings = Rating::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $ratings
        ]);
    }

    /**
     * @group Rating
     * Get a specific rating
     */
    public function show($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json([
                'status' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $rating
        ]);
    }

    /**
     * @group Rating
     * Create a new rating
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'profile_id' => 'required|integer',
            'rating_value' => 'required|integer|min:1|max:5',
            'review_comments' => 'nullable|string'
        ]);

        $rating = Rating::create([
            'booking_id' => $validated['booking_id'],
            'profile_id' => $validated['profile_id'],
            'user_id' => auth()->id(),
            'rating_value' => $validated['rating_value'],
            'review_comments' => $validated['review_comments'] ?? null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Rating created successfully',
            'data' => $rating
        ], 201);
    }

    /**
     * @group Rating
     * Update a rating
     */
    public function update(Request $request, $id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json([
                'status' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating_value' => 'sometimes|integer|min:1|max:5',
            'review_comments' => 'nullable|string'
        ]);

        $rating->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Rating updated successfully',
            'data' => $rating
        ]);
    }
}