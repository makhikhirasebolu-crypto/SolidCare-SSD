<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationApplication extends Model
{
    use HasFactory;

    protected $table = 'accommodation_applications';

    protected $fillable = [
        'user_id',
        'accommodation_room_id',
        'admission_processed_by_user_id',
        'requested_accommodation_room_id',
        'previous_accommodation_room_id',
        'full_name',
        'student_id',
        'contact_number',
        'national_id',
        'email',
        'marital_status',
        'nationality',
        'gender',
        'age',
        'faculty',
        'programme',
        'intake',
        'semester',
        'check_in_date',
        'address',
        'district',
        'village',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_contact',
        'special_conditions_remark',
        'has_physical_disability',
        'physical_disability_details',
        'has_high_blood_pressure',
        'has_diabetes',
        'has_asthma',
        'chronic_illness_other',
        'on_chronic_treatment',
        'treatment_frequency',
        'payment_method',
        'payment_phone_number',
        'payment_reference',
        'payment_amount',
        'payment_status',
        'payment_receipt_number',
        'payment_confirmed_by_user_id',
        'payment_confirmed_at',
        'paid_at',
        'checkout_date',
        'checkout_reason',
        'checkout_requested_at',
        'checked_out_at',
        'reallocation_status',
        'reallocation_reason',
        'reallocation_requested_at',
        'reallocation_decided_at',
        'reallocation_approved_by_user_id',
        'room_reallocated_by_user_id',
        'rejection_reason',
        'status',
    ];

    protected $casts = [
        'age' => 'integer',
        'check_in_date' => 'date',
        'has_physical_disability' => 'boolean',
        'has_high_blood_pressure' => 'boolean',
        'has_diabetes' => 'boolean',
        'has_asthma' => 'boolean',
        'on_chronic_treatment' => 'boolean',
        'payment_amount' => 'decimal:2',
        'payment_confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'checkout_date' => 'date',
        'checkout_requested_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'reallocation_requested_at' => 'datetime',
        'reallocation_decided_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(AccommodationRoom::class, 'accommodation_room_id');
    }

    public function admissionProcessedBy()
    {
        return $this->belongsTo(User::class, 'admission_processed_by_user_id');
    }

    public function paymentConfirmedBy()
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by_user_id');
    }

    public function requestedRoom()
    {
        return $this->belongsTo(AccommodationRoom::class, 'requested_accommodation_room_id');
    }

    public function previousRoom()
    {
        return $this->belongsTo(AccommodationRoom::class, 'previous_accommodation_room_id');
    }

    public function reallocationApprovedBy()
    {
        return $this->belongsTo(User::class, 'reallocation_approved_by_user_id');
    }

    public function roomReallocatedBy()
    {
        return $this->belongsTo(User::class, 'room_reallocated_by_user_id');
    }
}
