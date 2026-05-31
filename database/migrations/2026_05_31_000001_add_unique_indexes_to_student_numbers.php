<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->trimStudentIds('users');
        $this->trimStudentIds('students');

        $this->addUniqueStudentIdIndexIfClean('users', 'users_student_id_unique');
        $this->addUniqueStudentIdIndexIfClean('students', 'students_student_id_unique');
    }

    public function down(): void
    {
        $this->dropIndexIfPresent('students', 'students_student_id_unique');
        $this->dropIndexIfPresent('users', 'users_student_id_unique');
    }

    private function trimStudentIds(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'student_id')) {
            return;
        }

        DB::table($table)
            ->whereNotNull('student_id')
            ->update(['student_id' => DB::raw('NULLIF(TRIM(student_id), \'\')')]);
    }

    private function addUniqueStudentIdIndexIfClean(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'student_id') || $this->indexExists($table, $indexName)) {
            return;
        }

        if ($this->hasDuplicateStudentIds($table)) {
            Log::warning("Skipped {$indexName}; duplicate student numbers already exist in {$table}.");

            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->unique('student_id', $indexName);
        });
    }

    private function hasDuplicateStudentIds(string $table): bool
    {
        return DB::table($table)
            ->selectRaw('TRIM(student_id) as normalized_student_id')
            ->whereNotNull('student_id')
            ->whereRaw("TRIM(student_id) <> ''")
            ->groupByRaw('TRIM(student_id)')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function dropIndexIfPresent(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);
    }
};
