<?php

namespace App\Domains\Emergencies\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Emergency extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'emergency_type_id',
        'subtype',
        'description',
        'latitude',
        'longitude',
        'address',
        'status',
        'priority',
        'severity_score',
        'assigned_responder_id',
        'eta_minutes',
        'resolved_at',
        'triggered_via',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emergencyType()
    {
        return $this->belongsTo(EmergencyType::class);
    }

    public function assignedResponder()
    {
        return $this->belongsTo(\App\Domains\Responders\Models\Responder::class, 'assigned_responder_id');
    }
}
