<?php

namespace App\Domains\Responders\Models;

use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Responder extends Model
{
    protected $fillable = [
        'user_id',
        'responder_type',
        'vehicle_reg',
        'capacity',
        'current_lat',
        'current_lng',
        'is_available',
        'is_on_duty',
        'last_ping',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_on_duty' => 'boolean',
        'last_ping' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emergencies(): BelongsToMany
    {
        return $this->belongsToMany(Emergency::class)
            ->withPivot('status', 'assigned_at', 'arrived_at', 'completed_at')
            ->withTimestamps();
    }
}
