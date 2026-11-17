<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiary_applications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedTinyInteger('extra_type')->index();
            $table->string('full_name');
            $table->string('national_number', 20)->index();
            $table->string('mobile', 20)->index();
            $table->string('email')->nullable()->index();
            $table->unsignedTinyInteger('gender');
            $table->date('birth_date')->nullable();
            $table->string('country_code', 8)->nullable();
            $table->json('application_payload');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('accepted_patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('accepted_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('beneficiary_application_attachments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('beneficiary_application_id');
            $table->foreign('beneficiary_application_id', 'ba_attachments_application_fk')
                ->references('id')
                ->on('beneficiary_applications')
                ->cascadeOnDelete();
            $table->string('field_key', 64)->index();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiary_application_attachments');
        Schema::dropIfExists('beneficiary_applications');
    }
};
