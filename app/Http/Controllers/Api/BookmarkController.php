<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * @group Bookmark
     * Get all bookmarks for user profile
     */
    public function index(Request $request)
    {
        $bookmarks = $request->user()->bookmarks()->get();

        return response()->json([
            'bookmarks' => $bookmarks,
            'total' => $bookmarks->count(),
        ]);
    }

    /**
     * @group Bookmark
     * Create new bookmark for user profile
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $bookmark = $request->user()->bookmarks()->create([
            'profile_id' => $request->profile_id,
        ]);

        return response()->json([
            'message' => 'Bookmark created successfully',
            'bookmark' => $bookmark,
        ], 201);
    }

    /**
     * @group Bookmark
     * Update bookmark
     */
    public function update(Request $request, $id)
    {
        $bookmark = Bookmark::findOrFail($id);

        $request->validate([
            'id' => 'required|exists:bookmarks,id',
        ]);

        $bookmark->update([
            'profile_id' => $request->profile_id,
        ]);

        return response()->json([
            'message' => 'Bookmark updated successfully',
            'bookmark' => $bookmark,
        ]);
    }
}
