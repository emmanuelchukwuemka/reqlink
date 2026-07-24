<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'is_read',
        'admin_reply',
        'replied_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class);
    }
}
