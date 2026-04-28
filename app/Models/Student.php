<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_type',
        'student_id',
        'id_number',
        'disability',
        'disability_details',
    ];

    protected function casts(): array
    {
        return [
            'student_type' => 'string',
            'student_id' => 'string',
            'id_number' => 'string',
            'disability' => 'string',
            'disability_details' => 'string',
        ];
    }
}
