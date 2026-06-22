<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->unsignedTinyInteger('surgery_day_number')->nullable()->after('campaign_id');
            $table->unsignedSmallInteger('rank')->nullable()->after('surgery_day_number');
            $table->string('surgical_side', 20)->nullable()->after('admission_status');
            $table->text('approval_reason')->nullable()->after('eligibility_status_id');
            $table->json('screening_data')->nullable()->after('notes');

            $table->index(['campaign_id', 'surgery_day_number']);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropIndex(['campaign_id', 'surgery_day_number']);
            $table->dropColumn([
                'surgery_day_number',
                'rank',
                'surgical_side',
                'approval_reason',
                'screening_data',
            ]);
        });
    }
};
