<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounsellingBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_name',
        'student_identity_number',
        'sex',
        'reason',
        'programme',
        'year_of_study',
        'preferred_date',
        'preferred_time',
        'status',
        'appointment_date',
        'counsellor_notes',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'appointment_date' => 'datetime',
            'student_name' => 'string',
            'student_identity_number' => 'string',
            'sex' => 'string',
            'reason' => 'string',
            'programme' => 'string',
            'year_of_study' => 'string',
            'preferred_time' => 'string',
            'status' => 'string',
            'counsellor_notes' => 'string',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
