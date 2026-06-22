<?php

use App\Models\Campaign;
use App\Models\Patient;
use App\Support\RecordCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['campaign_id', 'file_number']);
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->unique('file_number');
        });

        $generator = app(RecordCodeGenerator::class);

        Campaign::query()->with('country')->orderBy('id')->each(function (Campaign $campaign) use ($generator): void {
            DB::table('campaigns')
                ->where('id', $campaign->id)
                ->update(['code' => $generator->generateCampaignCode($campaign)]);
        });

        Campaign::query()->with('country')->orderBy('id')->each(function (Campaign $campaign) use ($generator): void {
            $campaign->refresh();

            Patient::query()
                ->where('campaign_id', $campaign->id)
                ->orderBy('id')
                ->each(function (Patient $patient) use ($generator, $campaign): void {
                    DB::table('patients')
                        ->where('id', $patient->id)
                        ->update([
                            'file_number' => $generator->generatePatientFileNumber($campaign, $patient->id),
                        ]);
                });
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['file_number']);
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->unique(['campaign_id', 'file_number']);
        });
    }
};
