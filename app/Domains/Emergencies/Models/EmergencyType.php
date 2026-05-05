<?php

namespace App\Domains\Emergencies\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyType extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'description',
    ];

    public function emergencies(): HasMany
    {
        return $this->hasMany(Emergency::class);
    }
}
