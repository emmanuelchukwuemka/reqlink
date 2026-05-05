<?php

namespace App\Domains\Responders\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $fillable = [
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
}
