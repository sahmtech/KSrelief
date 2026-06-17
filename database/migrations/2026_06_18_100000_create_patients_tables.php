<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('patient_name');
            $table->string('file_number', 100)->nullable();
            $table->date('date_of_birth');
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->unsignedSmallInteger('age_months')->nullable();
            $table->string('gender', 20);
            $table->string('contact_number', 30)->nullable();
            $table->foreignId('eligibility_status_id')->constrained('patient_eligibility_statuses')->restrictOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('patient_stages')->nullOnDelete();
            $table->string('admission_status', 20)->default('not_admitted');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['campaign_id', 'file_number']);
            $table->index('patient_name');
            $table->index('admission_status');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('patient_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('storage_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_attachments');
        Schema::dropIfExists('patients');
    }
};
