<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained('activity_types')->restrictOnDelete();
            $table->foreignId('patient_stage_id')->nullable()->constrained('patient_stages')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('activity_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->string('status', 20)->default('planned');
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campaign_id', 'activity_date']);
            $table->index(['status', 'activity_date']);
            $table->index('activity_type_id');
        });

        Schema::create('activity_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('participant_type', 20);
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            $table->string('attendance_status', 20)->default('registered');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'member_id']);
            $table->unique(['activity_id', 'patient_id']);
            $table->index(['activity_id', 'participant_type']);
        });

        Schema::create('activity_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('event_type', 30)->default('status_change');
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_status_logs');
        Schema::dropIfExists('activity_participants');
        Schema::dropIfExists('activities');
    }
};
