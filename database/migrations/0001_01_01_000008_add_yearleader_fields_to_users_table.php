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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'faculty')) {
                $table->string('faculty')->nullable()->after('disability_details');
            }
            if (! Schema::hasColumn('users', 'class')) {
                $table->string('class')->nullable()->after('faculty');
            }
            if (! Schema::hasColumn('users', 'year')) {
                $table->string('year')->nullable()->after('class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'faculty')) {
                $table->dropColumn('faculty');
            }
            if (Schema::hasColumn('users', 'class')) {
                $table->dropColumn('class');
            }
            if (Schema::hasColumn('users', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};