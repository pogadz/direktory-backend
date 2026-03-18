<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSuggestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_title',
        'description',
        'upvotes',
        'status'
    ];

    /**
     * Relationship: JobSuggestion is upvoted by many Users
     */
    public function upvoters()
    {
        return $this->belongsToMany(User::class, 'job_suggestion_user')
                    ->withTimestamps();
    }
}
