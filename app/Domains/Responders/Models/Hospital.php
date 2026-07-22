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
        'resources',
    ];

    protected $casts = [
        'specialties' => 'array',
        'resources' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class);
    }

    public function emergencies()
    {
        return $this->hasMany(\App\Domains\Emergencies\Models\Emergency::class, 'target_hospital_id');
    }

    public function bedReservations()
    {
        return $this->hasMany(\App\Models\BedReservation::class);
    }
}
