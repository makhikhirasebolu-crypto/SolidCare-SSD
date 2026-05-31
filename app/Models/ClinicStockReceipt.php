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
        'expiry_date',
        'dosage_form',
        'important_notes',
        'expiry_month_notice_sent_at',
        'expiry_week_notice_sent_at',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'received_date' => 'date',
        'expiry_date' => 'date',
        'expiry_month_notice_sent_at' => 'datetime',
        'expiry_week_notice_sent_at' => 'datetime',
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
