<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accommodation_payments')) {
            return;
        }

        DB::table('accommodation_applications')
            ->whereNotNull('payment_receipt_number')
            ->where('payment_receipt_number', '!=', '')
            ->orderBy('id')
            ->each(function ($application): void {
                $receiptNumber = trim((string) $application->payment_receipt_number);

                if ($receiptNumber === '' || DB::table('accommodation_payments')->where('receipt_number', $receiptNumber)->exists()) {
                    return;
                }

                $paymentMonth = $this->legacyPaymentMonth($application);

                if (DB::table('accommodation_payments')
                    ->where('accommodation_application_id', $application->id)
                    ->whereDate('payment_month', $paymentMonth)
                    ->exists()) {
                    return;
                }

                DB::table('accommodation_payments')->insert([
                    'accommodation_application_id' => $application->id,
                    'receipt_number' => $receiptNumber,
                    'payment_month' => $paymentMonth,
                    'amount' => 500.00,
                    'method' => 'standard_lesotho_bank',
                    'status' => $application->payment_status ?: 'confirmed',
                    'confirmed_by_user_id' => $application->payment_confirmed_by_user_id,
                    'confirmed_at' => $application->payment_confirmed_at ?: now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        //
    }

    private function legacyPaymentMonth(object $application): string
    {
        if (isset($application->payment_month) && $application->payment_month) {
            return Carbon::parse($application->payment_month)->startOfMonth()->toDateString();
        }

        if (isset($application->payment_confirmed_at) && $application->payment_confirmed_at) {
            return Carbon::parse($application->payment_confirmed_at)->startOfMonth()->toDateString();
        }

        return now()->startOfMonth()->toDateString();
    }
};
