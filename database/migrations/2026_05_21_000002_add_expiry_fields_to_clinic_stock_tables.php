<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinic_stock_items', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_stock_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('quantity_issued');
            }
        });

        Schema::table('clinic_stock_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_stock_receipts', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('received_date');
            }
            if (! Schema::hasColumn('clinic_stock_receipts', 'expiry_month_notice_sent_at')) {
                $table->timestamp('expiry_month_notice_sent_at')->nullable()->after('expiry_date');
            }
            if (! Schema::hasColumn('clinic_stock_receipts', 'expiry_week_notice_sent_at')) {
                $table->timestamp('expiry_week_notice_sent_at')->nullable()->after('expiry_month_notice_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_stock_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_stock_receipts', 'expiry_week_notice_sent_at')) {
                $table->dropColumn('expiry_week_notice_sent_at');
            }
            if (Schema::hasColumn('clinic_stock_receipts', 'expiry_month_notice_sent_at')) {
                $table->dropColumn('expiry_month_notice_sent_at');
            }
            if (Schema::hasColumn('clinic_stock_receipts', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });

        Schema::table('clinic_stock_items', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_stock_items', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });
    }
};
