<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicStockComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_stock_item_id',
        'user_id',
        'parent_id',
        'message',
    ];

    public function stockItem()
    {
        return $this->belongsTo(ClinicStockItem::class, 'clinic_stock_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->with('user')->oldest();
    }
}
