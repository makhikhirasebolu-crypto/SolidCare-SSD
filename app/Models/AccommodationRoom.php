<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_name',
        'room_number',
        'capacity',
    ];

    public function applications()
    {
        return $this->hasMany(AccommodationApplication::class);
    }
}
