<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminActivityLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'subject_type', 'subject_id', 'description'];

    public function admin()
    {
        return $this->belongsTo(\App\Domains\Users\Models\User::class, 'admin_id');
    }

    public static function record(string $action, string $description, $subject = null): void
    {
        static::create([
            'admin_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
        ]);
    }
}
