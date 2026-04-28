<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_stock_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_stock_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id');
            $table->unsignedInteger('quantity_issued');
            $table->text('diagnosis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_stock_usages');
    }
};
