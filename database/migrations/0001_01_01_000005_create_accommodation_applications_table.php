<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accommodation_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('contact_number');
            $table->string('national_id');
            $table->string('email');
            $table->string('marital_status');
            $table->string('nationality');
            $table->integer('age');
            $table->string('faculty');
            $table->string('programme');
            $table->date('check_in_date');
            $table->text('address');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accommodation_applications');
    }
};
