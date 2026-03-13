<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Availability extends Model
{
    use SoftDeletes;

    protected $fillable = ['profile_id', 'schedule'];

    protected $casts = ['schedule' => 'array'];
}
