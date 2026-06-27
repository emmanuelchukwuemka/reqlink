<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedReservation extends Model
{
    protected $fillable = ['hospital_id', 'emergency_id', 'responder_id', 'status', 'eta_minutes', 'confirmed_at', 'arrived_at'];

    protected $casts = ['confirmed_at' => 'datetime', 'arrived_at' => 'datetime'];

    public function hospital()   { return $this->belongsTo(\App\Domains\Responders\Models\Hospital::class); }
    public function responder()  { return $this->belongsTo(\App\Domains\Responders\Models\Responder::class); }
    public function emergency()  { return $this->belongsTo(\App\Domains\Emergencies\Models\Emergency::class); }
}
