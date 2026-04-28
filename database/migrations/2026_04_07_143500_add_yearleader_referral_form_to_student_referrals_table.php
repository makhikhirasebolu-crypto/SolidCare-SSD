<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('student_referrals', 'yearleader_referral_form')) {
                $table->json('yearleader_referral_form')->nullable()->after('entry_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('student_referrals', 'yearleader_referral_form')) {
                $table->dropColumn('yearleader_referral_form');
            }
        });
    }
};
