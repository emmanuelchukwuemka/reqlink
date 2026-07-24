<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 'reference', 'description', 'status', 'is_flagged', 'flag_note',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class);
    }
}
