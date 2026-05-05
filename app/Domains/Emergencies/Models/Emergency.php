<?php

namespace App\Domains\Emergencies\Models;

use App\Domains\Responders\Models\Responder;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Emergency extends Model
{
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
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EmergencyType::class, 'emergency_type_id');
    }

    public function responders(): BelongsToMany
    {
        return $this->belongsToMany(Responder::class)
            ->withPivot('status', 'assigned_at', 'arrived_at', 'completed_at')
            ->withTimestamps();
    }
}
