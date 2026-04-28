<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            $table->foreignId('accommodation_room_id')
                ->nullable()
                ->after('user_id')
                ->constrained('accommodation_rooms')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accommodation_room_id');
        });
    }
};
