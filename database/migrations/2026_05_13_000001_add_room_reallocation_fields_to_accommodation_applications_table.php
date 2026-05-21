<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'requested_accommodation_room_id')) {
                $table->unsignedBigInteger('requested_accommodation_room_id')
                    ->nullable()
                    ->after('accommodation_room_id');
            }

            if (! Schema::hasColumn('accommodation_applications', 'reallocation_status')) {
                $table->string('reallocation_status')->nullable()->after('requested_accommodation_room_id');
            }

            if (! Schema::hasColumn('accommodation_applications', 'reallocation_reason')) {
                $table->text('reallocation_reason')->nullable()->after('reallocation_status');
            }

            if (! Schema::hasColumn('accommodation_applications', 'reallocation_requested_at')) {
                $table->timestamp('reallocation_requested_at')->nullable()->after('reallocation_reason');
            }

            if (! Schema::hasColumn('accommodation_applications', 'reallocation_decided_at')) {
                $table->timestamp('reallocation_decided_at')->nullable()->after('reallocation_requested_at');
            }
        });

        Schema::table('accommodation_applications', function (Blueprint $table) {
            $table->foreign('requested_accommodation_room_id', 'acc_apps_req_room_fk')
                ->references('id')
                ->on('accommodation_rooms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (Schema::hasColumn('accommodation_applications', 'requested_accommodation_room_id')) {
                $table->dropForeign('acc_apps_req_room_fk');
                $table->dropColumn('requested_accommodation_room_id');
            }

            $columns = [];

            foreach ([
                'reallocation_status',
                'reallocation_reason',
                'reallocation_requested_at',
                'reallocation_decided_at',
            ] as $column) {
                if (Schema::hasColumn('accommodation_applications', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
