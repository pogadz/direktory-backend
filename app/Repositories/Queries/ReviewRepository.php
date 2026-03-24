<?php

namespace App\Repositories\Queries;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * Get all reviews
     *
     * @param integer $perPage
     * @return Review
     */
    public function paginateLatest(int $perPage = 10)
    {
        return Review::latest()->paginate($perPage);
    }

    /**
     * Get specific review
     *
     * @param integer $id
     * @return Review|null
     */
    public function findById(int $id)
    {
        return Review::find($id);
    }

    /**
     * Create a new review
     *
     * @param array $data
     * @return Review
     */
    public function create(array $data)
    {
        return Review::create($data);
    }

    /**
     * Update a review
     *
     * @param integer $id
     * @param array $data
     * @return Review|null
     */
    public function update(int $id, array $data)
    {
        $review = Review::find($id);

        if (!$review) {
            return null;
        }

        $review->update($data);

        return $review;
    }
}