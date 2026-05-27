<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'payment_receipt_number')) {
                $table->string('payment_receipt_number')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_confirmed_by_user_id')) {
                $table->unsignedBigInteger('payment_confirmed_by_user_id')
                    ->nullable()
                    ->after('payment_receipt_number');
            }

            if (! Schema::hasColumn('accommodation_applications', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')->nullable()->after('payment_confirmed_by_user_id');
            }
        });

        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'payment_confirmed_by_user_id')) {
                $table->foreign('payment_confirmed_by_user_id', 'acc_apps_payment_confirmed_by_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'payment_confirmed_by_user_id')) {
                $table->dropForeign('acc_apps_payment_confirmed_by_fk');
            }

            $columns = [];

            foreach ([
                'payment_confirmed_at',
                'payment_confirmed_by_user_id',
                'payment_receipt_number',
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
