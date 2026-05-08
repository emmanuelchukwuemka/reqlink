<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'responder_id',
        'rating',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class);
    }

    public function responder()
    {
        return $this->belongsTo(\App\Domains\Responders\Models\Responder::class);
    }
}
