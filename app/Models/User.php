<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'student_type',
        'student_id',
        'id_number',
        'disability',
        'disability_details',
        'password_temporary',
        'temporary_password_expires_at',
        'temporary_password_plain',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'temporary_password_plain',
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
            'password_temporary' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'temporary_password_plain' => 'encrypted',
            'role' => 'string',
            'student_type' => 'string',
            'student_id' => 'string',
            'id_number' => 'string',
            'disability' => 'string',
            'disability_details' => 'string',
        ];
    }

    public function yearLeader()
    {
        return $this->hasOne(YearLeader::class);
    }
    public function referrals()
{
    return $this->hasMany(StudentReferral::class, 'referred_by');
}

public function referralComments()
{
    return $this->hasMany(ReferralComment::class);
}

}
