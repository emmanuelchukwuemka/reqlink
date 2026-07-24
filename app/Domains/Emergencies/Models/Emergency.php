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
        'target_hospital_id',
        'hospital_accepted_at',
        'evidence_file',
        'triage_data',
        'doctor_notes',
        'doctor_consult_requested_at',
        'consult_fee_paid_at',
        'hospital_decline_reason',
        'responder_notes',
        'admission_fee_paid_at',
    ];

    protected $casts = [
        'triage_data' => 'array',
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

    public function targetHospital()
    {
        return $this->belongsTo(\App\Domains\Responders\Models\Hospital::class, 'target_hospital_id');
    }

    /**
     * Free the assigned responder's availability so auto-dispatch can route new
     * emergencies to them again. Must be called whenever a mission ends (resolved,
     * cancelled, or declined) — without this the responder stays permanently
     * unavailable since nothing else in the app resets this flag.
     */
    public function freeAssignedResponder(): void
    {
        if ($this->assigned_responder_id) {
            \App\Domains\Responders\Models\Responder::where('id', $this->assigned_responder_id)
                ->update(['is_available' => true]);
        }
    }
}
