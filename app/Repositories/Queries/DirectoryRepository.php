<?php

namespace App\Repositories\Queries;

use App\Models\Directory;
use App\Repositories\Contracts\DirectoryRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class DirectoryRepository implements DirectoryRepositoryInterface
{
    /**
     * Get all directories
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Directory::latest()->get();
    }

    /**
     * Get specific directory
     *
     * @param integer $id
     * @return Directory|null
     */
    public function find(int $id): ?Directory
    {
        return Directory::find($id);
    }

    /**
     * Create a new directory
     *
     * @param array $data
     * @return Directory
     */
    public function create(array $data): Directory
    {
        $data['slug'] = Str::slug($data['name']);
        return Directory::create($data);
    }

    /**
     * Update a directory
     *
     * @param integer $id
     * @param array $data
     * @return Directory|null
     */
    public function update(int $id, array $data): ?Directory
    {
        $directory = Directory::find($id);
        if (!$directory) return null;

        $data['slug'] = Str::slug($data['name']);
        $directory->update($data);

        return $directory->fresh();
    }

    /**
     * Delete a directory
     *
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $directory = Directory::find($id);
        if (!$directory) return false;

        return $directory->delete();
    }
}