<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'profession',
        'status_emoji',
        'status_text',
        'location',
        'responseTime'
    ];

    /**
     * Relationship: UserDetails belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
