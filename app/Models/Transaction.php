<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'reference_type',
        'reference_id',
    ];

    const TYPE_BOOKING = 'BOOKING';
    const TYPE_PAYMENT = 'PAYMENT';
    const TYPE_REFUND  = 'REFUND';

    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_FAILED = 'FAILED';

    public function user()
    {
        return $this->belongsTo(User::class);
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
