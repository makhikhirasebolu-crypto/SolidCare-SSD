<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'admission_processed_by_user_id')) {
                $table->unsignedBigInteger('admission_processed_by_user_id')
                    ->nullable()
                    ->after('accommodation_room_id');
            }
        });

        Schema::table('accommodation_applications', function (Blueprint $table) {
            $table->foreign('admission_processed_by_user_id', 'acc_apps_admission_processed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'admission_processed_by_user_id')) {
                $table->dropForeign('acc_apps_admission_processed_by_fk');
                $table->dropColumn('admission_processed_by_user_id');
            }
        });
    }
};
