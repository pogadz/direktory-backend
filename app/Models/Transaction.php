<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'type',
        'amount',
        'status',
        'reference_type',
        'reference_id',
        'payment_intent_id'
    ];

    const TYPE_BOOKING = 'booking';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND  = 'refund';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
