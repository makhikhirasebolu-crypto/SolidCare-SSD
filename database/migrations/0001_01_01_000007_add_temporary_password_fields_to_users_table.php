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
            if (! Schema::hasColumn('users', 'password_temporary')) {
                $table->boolean('password_temporary')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'temporary_password_expires_at')) {
                $table->timestamp('temporary_password_expires_at')->nullable()->after('password_temporary');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('email');
            }
            if (! Schema::hasColumn('users', 'student_type')) {
                $table->string('student_type')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'student_id')) {
                $table->string('student_id')->nullable()->after('student_type');
            }
            if (! Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('users', 'disability')) {
                $table->string('disability')->nullable()->after('id_number');
            }
            if (! Schema::hasColumn('users', 'disability_details')) {
                $table->text('disability_details')->nullable()->after('disability');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_temporary')) {
                $table->dropColumn('password_temporary');
            }
            if (Schema::hasColumn('users', 'temporary_password_expires_at')) {
                $table->dropColumn('temporary_password_expires_at');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'student_type')) {
                $table->dropColumn('student_type');
            }
            if (Schema::hasColumn('users', 'student_id')) {
                $table->dropColumn('student_id');
            }
            if (Schema::hasColumn('users', 'id_number')) {
                $table->dropColumn('id_number');
            }
            if (Schema::hasColumn('users', 'disability')) {
                $table->dropColumn('disability');
            }
            if (Schema::hasColumn('users', 'disability_details')) {
                $table->dropColumn('disability_details');
            }
        });
    }
};