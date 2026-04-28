<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('student_referrals', 'ssd_attendance_form')) {
                $table->json('ssd_attendance_form')->nullable()->after('entry_type');
            }

            if (! Schema::hasColumn('student_referrals', 'ssd_attended_by')) {
                $table->foreignId('ssd_attended_by')->nullable()->after('referred_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('student_referrals', 'ssd_attended_at')) {
                $table->timestamp('ssd_attended_at')->nullable()->after('ssd_attended_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('student_referrals', 'ssd_attended_at')) {
                $table->dropColumn('ssd_attended_at');
            }

            if (Schema::hasColumn('student_referrals', 'ssd_attended_by')) {
                $table->dropConstrainedForeignId('ssd_attended_by');
            }

            if (Schema::hasColumn('student_referrals', 'ssd_attendance_form')) {
                $table->dropColumn('ssd_attendance_form');
            }
        });
    }
};
