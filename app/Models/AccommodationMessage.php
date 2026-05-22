<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user')->oldest();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
