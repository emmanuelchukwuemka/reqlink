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

    /**
     * Whether a real facility location has been set. New registrations default
     * lat/lng to 0,0 (Null Island) until the hospital sets its actual position.
     */
    public function hasLocation(): bool
    {
        return (float) $this->lat !== 0.0 || (float) $this->lng !== 0.0;
    }

    public function emergencies()
    {
        return $this->hasMany(\App\Domains\Emergencies\Models\Emergency::class, 'target_hospital_id');
    }

    public function bedReservations()
    {
        return $this->hasMany(\App\Models\BedReservation::class);
    }

    public function hospitalPatients()
    {
        return $this->hasMany(\App\Models\HospitalPatient::class);
    }

    public function manualReservations()
    {
        return $this->hasMany(\App\Models\HospitalReservation::class);
    }
}
