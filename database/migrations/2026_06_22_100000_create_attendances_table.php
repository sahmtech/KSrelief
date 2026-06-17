<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->unsignedTinyInteger('shift_number')->default(1);
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->foreignId('attendance_status_id')->constrained('attendance_statuses')->restrictOnDelete();
            $table->unsignedInteger('worked_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['campaign_id', 'member_id', 'attendance_date', 'shift_number'],
                'attendances_campaign_member_date_shift_unique'
            );
            $table->index(['campaign_id', 'attendance_date']);
            $table->index(['member_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
