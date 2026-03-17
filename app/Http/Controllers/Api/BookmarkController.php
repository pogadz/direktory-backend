<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BookmarkRepositoryInterface;

class BookmarkController extends Controller
{
    protected $bookmarks;

    public function __construct(BookmarkRepositoryInterface $bookmarks)
    {
        $this->bookmarks = $bookmarks;
    }

    /**
     * @group Bookmark
     * Get all bookmarks for the current bookmarker
     */
    public function index(Request $request)
    {
        $bookmarker = $request->user();

        $bookmarks = $this->bookmarks->getByBookmarker($bookmarker);

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

        $bookmarker = $request->user();
        $profileId = $request->profile_id;

        $result = $this->bookmarks->toggleBookmark($bookmarker, $profileId);

        $message = $result['action'] === 'created'
            ? 'Bookmark created successfully'
            : 'Bookmark removed successfully';

        return response()->json([
            'message' => $message,
            'bookmark' => $result['bookmark']
        ], $result['action'] === 'created' ? 201 : 200);
    }

    /**
     * @group Bookmark
     * Delete a bookmark
     */
    public function destroy(Request $request, $id)
    {
        $bookmarker = $request->user();

        $this->bookmarks->deleteBookmark($bookmarker, $id);

        return response()->json([
            'message' => 'Bookmark deleted successfully',
        ]);
    }
}
