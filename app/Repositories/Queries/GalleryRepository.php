<?php

namespace App\Repositories\Queries;

use App\Models\Gallery;
use App\Models\Profile;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GalleryRepository implements GalleryRepositoryInterface
{
    public function allByProfile(int $profileId): Collection
    {
        return Gallery::where('profile_id', $profileId)->latest()->get();
    }

    public function find(int $profileId, int $id): ?Gallery
    {
        return Gallery::where('profile_id', $profileId)->find($id);
    }

    public function create(int $profileId, array $data): Gallery
    {
        $data['profile_id'] = $profileId;
        return Gallery::create($data);
    }

    public function update(int $profileId, int $id, array $data): ?Gallery
    {
        $item = $this->find($profileId, $id);
        if (!$item) return null;

        $item->update($data);
        return $item->fresh();
    }

    public function delete(int $profileId, int $id): bool
    {
        $item = $this->find($profileId, $id);
        if (!$item) return false;

        return $item->delete();
    }
}