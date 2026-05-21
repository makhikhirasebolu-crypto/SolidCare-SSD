<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'previous_accommodation_room_id')) {
                $table->unsignedBigInteger('previous_accommodation_room_id')
                    ->nullable()
                    ->after('requested_accommodation_room_id');
            }

            if (! Schema::hasColumn('accommodation_applications', 'reallocation_approved_by_user_id')) {
                $table->unsignedBigInteger('reallocation_approved_by_user_id')
                    ->nullable()
                    ->after('reallocation_decided_at');
            }

            if (! Schema::hasColumn('accommodation_applications', 'room_reallocated_by_user_id')) {
                $table->unsignedBigInteger('room_reallocated_by_user_id')
                    ->nullable()
                    ->after('reallocation_approved_by_user_id');
            }
        });

        Schema::table('accommodation_applications', function (Blueprint $table) {
            $table->foreign('previous_accommodation_room_id', 'acc_apps_prev_room_fk')
                ->references('id')
                ->on('accommodation_rooms')
                ->nullOnDelete();

            $table->foreign('reallocation_approved_by_user_id', 'acc_apps_realloc_approved_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('room_reallocated_by_user_id', 'acc_apps_room_reallocated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'previous_accommodation_room_id')) {
                $table->dropForeign('acc_apps_prev_room_fk');
                $table->dropColumn('previous_accommodation_room_id');
            }

            if (Schema::hasColumn('accommodation_applications', 'reallocation_approved_by_user_id')) {
                $table->dropForeign('acc_apps_realloc_approved_by_fk');
                $table->dropColumn('reallocation_approved_by_user_id');
            }

            if (Schema::hasColumn('accommodation_applications', 'room_reallocated_by_user_id')) {
                $table->dropForeign('acc_apps_room_reallocated_by_fk');
                $table->dropColumn('room_reallocated_by_user_id');
            }
        });
    }
};
