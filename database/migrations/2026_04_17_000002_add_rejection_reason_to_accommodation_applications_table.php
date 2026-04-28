<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('checked_out_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
