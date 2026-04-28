<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('accommodation_applications', 'student_id')) {
                $table->string('student_id')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'gender')) {
                $table->string('gender')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'intake')) {
                $table->string('intake')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'semester')) {
                $table->string('semester')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'district')) {
                $table->string('district')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'village')) {
                $table->string('village')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'next_of_kin_name')) {
                $table->string('next_of_kin_name')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'next_of_kin_relationship')) {
                $table->string('next_of_kin_relationship')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'next_of_kin_contact')) {
                $table->string('next_of_kin_contact')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'special_conditions_remark')) {
                $table->text('special_conditions_remark')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'has_physical_disability')) {
                $table->boolean('has_physical_disability')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'physical_disability_details')) {
                $table->text('physical_disability_details')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'has_high_blood_pressure')) {
                $table->boolean('has_high_blood_pressure')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'has_diabetes')) {
                $table->boolean('has_diabetes')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'has_asthma')) {
                $table->boolean('has_asthma')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'chronic_illness_other')) {
                $table->text('chronic_illness_other')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'on_chronic_treatment')) {
                $table->boolean('on_chronic_treatment')->nullable();
            }

            if (! Schema::hasColumn('accommodation_applications', 'treatment_frequency')) {
                $table->text('treatment_frequency')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_applications', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'student_id',
                'gender',
                'intake',
                'semester',
                'district',
                'village',
                'next_of_kin_name',
                'next_of_kin_relationship',
                'next_of_kin_contact',
                'special_conditions_remark',
                'has_physical_disability',
                'physical_disability_details',
                'has_high_blood_pressure',
                'has_diabetes',
                'has_asthma',
                'chronic_illness_other',
                'on_chronic_treatment',
                'treatment_frequency',
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
