<?php
// app/Models/StudentReferral.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentReferral extends Model
{
    protected $fillable = [
        'student_user_id', 'student_name', 'student_id', 'programme', 'entry_type', 'reason',
        'priority', 'status', 'referred_by', 'yearleader_referral_form', 'ssd_attendance_form',
        'ssd_attended_by', 'ssd_attended_at'
    ];

    protected $casts = [
        'student_user_id' => 'integer',
        'entry_type' => 'string',
        'yearleader_referral_form' => 'array',
        'ssd_attendance_form' => 'array',
        'ssd_attended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReferralComment::class)->whereNull('parent_id')->latest();
    }
}
