<?php

namespace App\Repositories\Queries;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function paginateLatest(int $perPage = 10)
    {
        return Review::latest()->paginate($perPage);
    }

    public function findById(int $id)
    {
        return Review::find($id);
    }

    public function create(array $data)
    {
        return Review::create($data);
    }

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