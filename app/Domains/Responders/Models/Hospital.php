<?php

namespace App\Domains\Responders\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'lat',
        'lng',
        'total_beds',
        'available_beds',
        'icu_beds',
        'contact_phone',
        'specialties',
    ];

    protected $casts = [
        'specialties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class);
    }
}
