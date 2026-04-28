<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_stock_usages', function (Blueprint $table) {
            $table->date('usage_date')->nullable()->after('diagnosis');
        });

        DB::table('clinic_stock_usages')
            ->whereNull('usage_date')
            ->update([
                'usage_date' => DB::raw('DATE(created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('clinic_stock_usages', function (Blueprint $table) {
            $table->dropColumn('usage_date');
        });
    }
};
