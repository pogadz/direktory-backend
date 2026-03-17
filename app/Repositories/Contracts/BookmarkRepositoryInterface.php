<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface BookmarkRepositoryInterface
{
    public function getByBookmarker(object $bookmarker): Collection;

    public function toggleBookmark(object $bookmarker, int $profileId): array;

    public function deleteBookmark(object $bookmarker, int $bookmarkId): bool;
}