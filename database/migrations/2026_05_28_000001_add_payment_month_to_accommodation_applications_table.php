<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'payment_month')) {
                $table->date('payment_month')->nullable()->after('payment_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'payment_month')) {
                $table->dropColumn('payment_month');
            }
        });
    }
};
