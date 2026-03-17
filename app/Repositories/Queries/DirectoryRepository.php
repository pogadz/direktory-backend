<?php

namespace App\Repositories\Queries;

use App\Models\Directory;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class DirectoryRepository implements DirectoryRepositoryInterface
{
    public function all(): Collection
    {
        return Directory::latest()->get();
    }

    public function find(int $id): ?Directory
    {
        return Directory::find($id);
    }

    public function create(array $data): Directory
    {
        $data['slug'] = Str::slug($data['name']);
        return Directory::create($data);
    }

    public function update(int $id, array $data): ?Directory
    {
        $directory = Directory::find($id);
        if (!$directory) return null;

        $data['slug'] = Str::slug($data['name']);
        $directory->update($data);

        return $directory->fresh();
    }

    public function delete(int $id): bool
    {
        $directory = Directory::find($id);
        if (!$directory) return false;

        return $directory->delete();
    }
}