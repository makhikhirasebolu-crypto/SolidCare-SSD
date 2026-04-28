<?php
// database/migrations/2026_04_07_000001_create_student_referrals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_referrals', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('student_id');
            $table->string('programme')->nullable();
            $table->text('reason');
            $table->enum('priority', ['Normal', 'Urgent', 'Critical'])->default('Normal');
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->foreignId('referred_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('referral_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_referral_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->foreignId('parent_id')->nullable()->constrained('referral_comments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referral_comments');
        Schema::dropIfExists('student_referrals');
    }
};