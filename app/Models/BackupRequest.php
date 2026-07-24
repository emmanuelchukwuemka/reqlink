<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRequest extends Model
{
    protected $fillable = ['responder_id', 'emergency_id', 'lat', 'lng', 'message', 'status'];

    public function responder()
    {
        return $this->belongsTo(\App\Domains\Responders\Models\Responder::class);
    }

    public function emergency()
    {
        return $this->belongsTo(\App\Domains\Emergencies\Models\Emergency::class);
    }
}
