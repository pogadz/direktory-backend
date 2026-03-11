<?php

namespace App\Repositories\Queries;

use App\Models\Rating;
use App\Repositories\Contracts\RatingRepositoryInterface;

class RatingRepository implements RatingRepositoryInterface
{
    public function all()
    {
        return Rating::latest()->paginate(10);
    }

    public function find($id)
    {
        return Rating::find($id);
    }

    public function create(array $data)
    {
        return Rating::create($data);
    }

    public function update($id, array $data)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return null;
        }

        $rating->update($data);

        return $rating;
    }

    public function delete($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return false;
        }

        return $rating->delete();
    }
}