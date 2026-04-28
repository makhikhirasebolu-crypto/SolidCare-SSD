<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('student_referrals', 'entry_type')) {
                $table->string('entry_type')->default('referral')->after('programme');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('student_referrals', 'entry_type')) {
                $table->dropColumn('entry_type');
            }
        });
    }
};
