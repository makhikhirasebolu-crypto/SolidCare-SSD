<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'checkout_date')) {
                $table->date('checkout_date')->nullable()->after('check_in_date');
            }

            if (! Schema::hasColumn('accommodation_applications', 'checkout_reason')) {
                $table->text('checkout_reason')->nullable()->after('checkout_date');
            }

            if (! Schema::hasColumn('accommodation_applications', 'checkout_requested_at')) {
                $table->timestamp('checkout_requested_at')->nullable()->after('checkout_reason');
            }

            if (! Schema::hasColumn('accommodation_applications', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('checkout_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            $columns = [];

            foreach (['checkout_date', 'checkout_reason', 'checkout_requested_at', 'checked_out_at'] as $column) {
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
