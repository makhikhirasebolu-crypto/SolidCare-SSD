<?php
// app/Models/ReferralComment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralComment extends Model
{
    protected $fillable = ['student_referral_id', 'user_id', 'message', 'parent_id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReferralComment::class, 'parent_id')->latest();
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(StudentReferral::class);
    }
}