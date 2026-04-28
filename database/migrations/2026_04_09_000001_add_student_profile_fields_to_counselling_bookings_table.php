<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counselling_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('counselling_bookings', 'sex')) {
                $table->string('sex', 50)->nullable()->after('student_identity_number');
            }

            if (! Schema::hasColumn('counselling_bookings', 'programme')) {
                $table->string('programme')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('counselling_bookings', 'year_of_study')) {
                $table->string('year_of_study', 50)->nullable()->after('programme');
            }
        });
    }

    public function down(): void
    {
        Schema::table('counselling_bookings', function (Blueprint $table) {
            $columns = ['sex', 'programme', 'year_of_study'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('counselling_bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
