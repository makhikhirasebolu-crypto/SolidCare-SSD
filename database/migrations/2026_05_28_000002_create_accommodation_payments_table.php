<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_application_id')
                ->constrained('accommodation_applications')
                ->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->date('payment_month');
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('status')->default('confirmed');
            $table->foreignId('confirmed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['accommodation_application_id', 'payment_month'], 'acc_payments_app_month_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_payments');
    }
};
