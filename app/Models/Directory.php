<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Directory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug'];

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
