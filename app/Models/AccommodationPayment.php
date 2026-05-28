<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_application_id',
        'receipt_number',
        'payment_month',
        'amount',
        'method',
        'status',
        'confirmed_by_user_id',
        'confirmed_at',
    ];

    protected $casts = [
        'payment_month' => 'date',
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(AccommodationApplication::class, 'accommodation_application_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}
