<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_user_id',
        'sender_profile_id',
        'body',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderProfile()
    {
        return $this->belongsTo(Profile::class, 'sender_profile_id');
    }
}
