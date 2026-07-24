<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['admin_id', 'title', 'message', 'target_role', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function admin()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class, 'admin_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
