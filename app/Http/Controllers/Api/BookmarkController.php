<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookmark;
use App\Models\Profile;

class BookmarkController extends Controller
{
    /**
     * @group Bookmark
     * Get all bookmarks for the current bookmarker
     */
    public function index(Request $request)
    {
        $bookmarker = $request->user(); // assuming authenticated user is the bookmarker

        $bookmarks = Bookmark::where('bookmarker_id', $bookmarker->id)
                             ->where('bookmarker_type', get_class($bookmarker))
                             ->with('profile') // eager load the bookmarked profile
                             ->get();

        return response()->json([
            'bookmarks' => $bookmarks,
            'total' => $bookmarks->count(),
        ]);
    }

    /**
     * @group Bookmark
     * Create or toggle a bookmark
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $bookmarker = $request->user(); // could also be a profile if you allow profile bookmarkers

        $profileId = $request->profile_id;

        // Check if the bookmark already exists
        $bookmark = Bookmark::where('bookmarker_id', $bookmarker->id)
                            ->where('bookmarker_type', get_class($bookmarker))
                            ->where('profile_id', $profileId)
                            ->first();

        if ($bookmark) {
            // Remove bookmark if it exists (toggle behavior)
            $bookmark->delete();
            return response()->json([
                'message' => 'Bookmark removed successfully',
            ]);
        }

        // Create a new bookmark
        $bookmark = Bookmark::create([
            'bookmarker_id' => $bookmarker->id,
            'bookmarker_type' => get_class($bookmarker),
            'profile_id' => $profileId,
        ]);

        return response()->json([
            'message' => 'Bookmark created successfully',
            'bookmark' => $bookmark,
        ], 201);
    }

    /**
     * @group Bookmark
     * Delete a bookmark
     */
    public function destroy(Request $request, $id)
    {
        $bookmarker = $request->user();

        $bookmark = Bookmark::where('id', $id)
            ->where('bookmarker_id', $bookmarker->id)
            ->where('bookmarker_type', get_class($bookmarker))
            ->firstOrFail();

        $bookmark->delete();

        return response()->json([
            'message' => 'Bookmark deleted successfully',
        ]);
    }
}