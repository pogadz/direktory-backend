<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'action_type',
        'transaction_type',
        'transaction_id',
        'reference_type',
        'reference_id',
    ];

    const ACTION_TOPUP = 'TOPUP';
    const ACTION_DEDUCT = 'DEDUCT';
    const ACTION_REFUND = 'REFUND';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
