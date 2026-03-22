<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'directory_id',
        'job_category_id',
        'note',
        'requested_at',
        'accepted_at',
        'completed_at',
        'cancelled_at',
        'status'
    ];

    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function directory()
    {
        return $this->belongsTo(Directory::class);
    }

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class);
    }

}
