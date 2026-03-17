<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Gallery;

interface GalleryRepositoryInterface
{
    public function allByProfile(int $profileId): Collection;

    public function find(int $profileId, int $id): ?Gallery;

    public function create(int $profileId, array $data): Gallery;

    public function update(int $profileId, int $id, array $data): ?Gallery;

    public function delete(int $profileId, int $id): bool;
}