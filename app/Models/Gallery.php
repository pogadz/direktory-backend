<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'gallery';

    protected $fillable = [
        'profile_id',
        'image',
        'title',
        'description',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
