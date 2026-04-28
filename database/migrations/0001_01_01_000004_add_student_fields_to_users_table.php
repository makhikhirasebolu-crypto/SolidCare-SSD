<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email');
            $table->string('student_type')->nullable()->after('role');
            $table->string('student_id')->nullable()->after('student_type');
            $table->string('id_number')->nullable()->after('student_id');
            $table->string('disability')->default('no')->after('id_number');
            $table->text('disability_details')->nullable()->after('disability');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'student_type', 'student_id', 'id_number', 'disability', 'disability_details']);
        });
    }
};
