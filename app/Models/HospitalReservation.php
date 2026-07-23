<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalReservation extends Model
{
    protected $fillable = ['hospital_id', 'patient_name', 'bed_type', 'expected_at', 'notes', 'status'];

    protected $casts = ['expected_at' => 'datetime'];

    public function hospital() { return $this->belongsTo(\App\Domains\Responders\Models\Hospital::class); }
}
