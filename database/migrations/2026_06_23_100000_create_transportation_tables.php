<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportation_trips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('trip_code', 30)->unique();
            $table->date('trip_date');
            $table->time('departure_time');
            $table->time('arrival_time')->nullable();
            $table->foreignId('from_location_id')->constrained('transportation_locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->constrained('transportation_locations')->restrictOnDelete();
            $table->string('trip_type', 30);
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('planned');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campaign_id', 'trip_date']);
            $table->index(['status', 'trip_date']);
        });

        Schema::create('transportation_trip_passengers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained('transportation_trips')->cascadeOnDelete();
            $table->string('passenger_type', 20);
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['trip_id', 'member_id']);
            $table->unique(['trip_id', 'patient_id']);
            $table->index(['trip_id', 'passenger_type']);
        });

        Schema::create('transportation_trip_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained('transportation_trips')->cascadeOnDelete();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_trip_status_logs');
        Schema::dropIfExists('transportation_trip_passengers');
        Schema::dropIfExists('transportation_trips');
    }
};
