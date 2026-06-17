<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('file_name');
            $table->string('original_file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->string('status', 30)->default('uploaded');
            $table->foreignId('imported_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('patient_import_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('patient_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('patient_name')->nullable();
            $table->string('file_number', 100)->nullable();
            $table->json('validation_errors')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->boolean('is_duplicate')->default(false);
            $table->string('duplicate_reason')->nullable();
            $table->json('raw_data');
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['batch_id', 'row_number']);
            $table->index(['batch_id', 'is_valid']);
            $table->index(['batch_id', 'is_duplicate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_import_logs');
        Schema::dropIfExists('patient_import_batches');
    }
};
