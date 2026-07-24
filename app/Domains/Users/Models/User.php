<?php

namespace App\Domains\Users\Models;

use App\Domains\Users\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordCode($token));
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'is_verified',
        'is_suspended',
        'blood_group',
        'allergies',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'is_good_samaritan',
        'samaritan_profession',
        'samaritan_active',
        'last_known_lat',
        'last_known_lng',
        'wallet_balance',
        'license_path',
        'additional_docs_path',
        'mama_care_active',
        'pregnancy_due_date',
        'pregnancy_high_risk',
        'preferred_maternity_hospital',
        'obgyn_contact',
        'verification_rejected_reason',
        'verification_reviewed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_suspended' => 'boolean',
            'mama_care_active' => 'boolean',
            'pregnancy_due_date' => 'date',
            'pregnancy_high_risk' => 'boolean',
            'verification_reviewed_at' => 'datetime',
        ];
    }
}
