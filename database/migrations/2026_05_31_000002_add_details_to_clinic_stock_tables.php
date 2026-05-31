<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_stock_items', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_stock_items', 'dosage_form')) {
                $table->string('dosage_form')->nullable()->after('expiry_date');
            }

            if (! Schema::hasColumn('clinic_stock_items', 'important_notes')) {
                $table->text('important_notes')->nullable()->after('dosage_form');
            }
        });

        Schema::table('clinic_stock_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_stock_receipts', 'dosage_form')) {
                $table->string('dosage_form')->nullable()->after('expiry_date');
            }

            if (! Schema::hasColumn('clinic_stock_receipts', 'important_notes')) {
                $table->text('important_notes')->nullable()->after('dosage_form');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_stock_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_stock_receipts', 'important_notes')) {
                $table->dropColumn('important_notes');
            }

            if (Schema::hasColumn('clinic_stock_receipts', 'dosage_form')) {
                $table->dropColumn('dosage_form');
            }
        });

        Schema::table('clinic_stock_items', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_stock_items', 'important_notes')) {
                $table->dropColumn('important_notes');
            }

            if (Schema::hasColumn('clinic_stock_items', 'dosage_form')) {
                $table->dropColumn('dosage_form');
            }
        });
    }
};
