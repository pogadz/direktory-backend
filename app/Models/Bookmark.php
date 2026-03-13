<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = ['bookmarker_id', 'bookmarker_type', 'profile_id'];

    // Who did the bookmarking (User or Profile)
    public function bookmarker()
    {
        return $this->morphTo();
    }

    // Who is being bookmarked (always a profile)
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
