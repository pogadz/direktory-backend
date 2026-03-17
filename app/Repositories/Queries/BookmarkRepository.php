<?php

namespace App\Repositories\Queries;

use App\Models\Bookmark;
use App\Repositories\Contracts\BookmarkRepositoryInterface;

class BookmarkRepository implements BookmarkRepositoryInterface
{
    public function getByBookmarker(object $bookmarker)
    {
        return Bookmark::where('bookmarker_id', $bookmarker->id)
            ->where('bookmarker_type', get_class($bookmarker))
            ->with('profile')
            ->get();
    }

    public function toggleBookmark(object $bookmarker, int $profileId)
    {
        $bookmark = Bookmark::where('bookmarker_id', $bookmarker->id)
            ->where('bookmarker_type', get_class($bookmarker))
            ->where('profile_id', $profileId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return [
                'action' => 'removed',
                'bookmark' => null
            ];
        }

        $bookmark = Bookmark::create([
            'bookmarker_id' => $bookmarker->id,
            'bookmarker_type' => get_class($bookmarker),
            'profile_id' => $profileId,
        ]);

        return [
            'action' => 'created',
            'bookmark' => $bookmark
        ];
    }

    public function deleteBookmark(object $bookmarker, int $bookmarkId)
    {
        $bookmark = Bookmark::where('id', $bookmarkId)
            ->where('bookmarker_id', $bookmarker->id)
            ->where('bookmarker_type', get_class($bookmarker))
            ->firstOrFail();

        return $bookmark->delete();
    }
}