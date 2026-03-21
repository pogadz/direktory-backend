<?php

namespace App\Repositories\Queries;

use App\Models\JobCategory;
use App\Repositories\Contracts\JobCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class JobCategoryRepository implements JobCategoryRepositoryInterface
{
    /**
     * Get all job category
     *
     * @return Collection
     */
    public function allOrderedByName(): Collection
    {
        return JobCategory::orderBy('name')->get();
    }

    /**
     * Get specific job category
     *
     * @param integer $id
     * @return JobCategory|null
     */
    public function findById(int $id): ?JobCategory
    {
        return JobCategory::find($id);
    }

    /**
     * Create job category
     *
     * @param array $data
     * @return JobCategory
     */
    public function create(array $data): JobCategory
    {
        return JobCategory::create($data);
    }

    /**
     * Update job category
     *
     * @param integer $id
     * @param array $data
     * @return JobCategory|null
     */
    public function update(int $id, array $data): ?JobCategory
    {
        $category = JobCategory::find($id);

        if (!$category) {
            return null;
        }

        $category->update($data);

        return $category->fresh();
    }

    /**
     * Delete job category
     *
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $category = JobCategory::find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }
}