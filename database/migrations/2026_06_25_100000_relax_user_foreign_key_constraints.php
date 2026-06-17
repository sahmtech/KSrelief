<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<array{table: string, column: string}> */
    private array $userReferences = [
        ['table' => 'campaigns', 'column' => 'created_by'],
        ['table' => 'activities', 'column' => 'created_by'],
        ['table' => 'activity_status_logs', 'column' => 'changed_by'],
        ['table' => 'transportation_trips', 'column' => 'created_by'],
        ['table' => 'transportation_trip_status_logs', 'column' => 'changed_by'],
        ['table' => 'attendances', 'column' => 'recorded_by'],
        ['table' => 'patient_stage_histories', 'column' => 'changed_by'],
        ['table' => 'medical_records', 'column' => 'submitted_by'],
        ['table' => 'patient_import_batches', 'column' => 'imported_by'],
    ];

    public function up(): void
    {
        foreach ($this->userReferences as $reference) {
            $this->relaxUserForeignKey($reference['table'], $reference['column']);
        }
    }

    public function down(): void
    {
        foreach ($this->userReferences as $reference) {
            $this->restoreUserForeignKey($reference['table'], $reference['column']);
        }
    }

    private function relaxUserForeignKey(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $constraint = "{$table}_{$column}_foreign";

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`) REFERENCES `users` (`id`) ON DELETE SET NULL"
        );
    }

    private function restoreUserForeignKey(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $constraint = "{$table}_{$column}_foreign";

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL");
        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`{$column}`) REFERENCES `users` (`id`) ON DELETE RESTRICT"
        );
    }
};
