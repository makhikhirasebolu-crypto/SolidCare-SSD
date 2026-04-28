<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symptoms',
        'diagnosis',
        'treatment',
        'status',
        'appointment_date',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
