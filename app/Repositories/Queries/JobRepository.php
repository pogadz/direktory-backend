<?php

namespace App\Repositories\Queries;

use App\Models\JobCategory;
use App\Repositories\Contracts\JobCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class JobCategoryRepository implements JobCategoryRepositoryInterface
{
    public function allOrderedByName(): Collection
    {
        return JobCategory::orderBy('name')->get();
    }

    public function findById(int $id): ?JobCategory
    {
        return JobCategory::find($id);
    }

    public function create(array $data): JobCategory
    {
        return JobCategory::create($data);
    }

    public function update(int $id, array $data): ?JobCategory
    {
        $category = JobCategory::find($id);

        if (!$category) {
            return null;
        }

        $category->update($data);

        return $category->fresh();
    }

    public function delete(int $id): bool
    {
        $category = JobCategory::find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }
}