<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicStockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_name',
        'opening_stock',
        'quantity_received',
        'quantity_issued',
        'status',
        'confirmed_at',
        'confirmed_by_user_id',
    ];

    protected $casts = [
        'opening_stock' => 'integer',
        'quantity_received' => 'integer',
        'quantity_issued' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    public function comments()
    {
        return $this->hasMany(ClinicStockComment::class)->whereNull('parent_id')->latest();
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function getBalanceAttribute(): int
    {
        return max(0, $this->opening_stock + $this->quantity_received - $this->quantity_issued);
    }
}
