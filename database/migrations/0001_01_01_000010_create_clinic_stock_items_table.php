<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_name');
            $table->unsignedInteger('opening_stock')->default(0);
            $table->unsignedInteger('quantity_received')->default(0);
            $table->unsignedInteger('quantity_issued')->default(0);
            $table->string('status')->default('pending_review');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_stock_items');
    }
};
