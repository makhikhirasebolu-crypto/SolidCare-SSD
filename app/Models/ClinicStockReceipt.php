<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicStockReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_stock_item_id',
        'user_id',
        'quantity_received',
        'received_date',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'received_date' => 'date',
    ];

    public function stockItem()
    {
        return $this->belongsTo(ClinicStockItem::class, 'clinic_stock_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
