<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counselling_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('counselling_bookings', 'campus')) {
                $table->string('campus', 50)->nullable()->after('student_identity_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('counselling_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('counselling_bookings', 'campus')) {
                $table->dropColumn('campus');
            }
        });
    }
};
