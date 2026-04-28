<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearLeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty',
        'class',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'faculty' => 'string',
            'class' => 'string',
            'year' => 'string',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
