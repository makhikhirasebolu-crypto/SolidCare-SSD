<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('block_name');
            $table->unsignedInteger('room_number');
            $table->unsignedInteger('capacity')->default(4);
            $table->timestamps();

            $table->unique(['block_name', 'room_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_rooms');
    }
};
