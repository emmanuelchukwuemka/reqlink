<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['emergency_uuid', 'sender_id', 'sender_role', 'message', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function sender()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class, 'sender_id');
    }
}
