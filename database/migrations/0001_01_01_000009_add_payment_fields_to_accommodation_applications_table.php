<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('address');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_phone_number')) {
                $table->string('payment_phone_number')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_phone_number');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_amount');
            }

            if (! Schema::hasColumn('accommodation_applications', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'payment_method',
                'payment_phone_number',
                'payment_reference',
                'payment_amount',
                'payment_status',
                'paid_at',
            ] as $column) {
                if (Schema::hasColumn('accommodation_applications', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
