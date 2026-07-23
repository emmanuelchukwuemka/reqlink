<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalPatient extends Model
{
    protected $fillable = ['hospital_id', 'name', 'phone', 'reason', 'bed_type', 'status', 'notes', 'admitted_at', 'discharged_at'];

    protected $casts = ['admitted_at' => 'datetime', 'discharged_at' => 'datetime'];

    public function hospital() { return $this->belongsTo(\App\Domains\Responders\Models\Hospital::class); }
}
