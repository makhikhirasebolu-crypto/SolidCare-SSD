<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselling_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');
            $table->string('student_identity_number')->nullable();
            $table->text('reason');
            $table->date('preferred_date');
            $table->string('preferred_time', 20)->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('appointment_date')->nullable();
            $table->text('counsellor_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_bookings');
    }
};
