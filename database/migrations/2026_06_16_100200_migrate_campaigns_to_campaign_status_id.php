<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['name' => 'Draft', 'code' => 'draft', 'color' => '#64748B', 'is_default' => true],
            ['name' => 'Planned', 'code' => 'planned', 'color' => '#3B82F6', 'is_default' => false],
            ['name' => 'Active', 'code' => 'active', 'color' => '#22C55E', 'is_default' => false],
            ['name' => 'Completed', 'code' => 'completed', 'color' => '#0F766E', 'is_default' => false],
            ['name' => 'Cancelled', 'code' => 'cancelled', 'color' => '#EF4444', 'is_default' => false],
        ];

        foreach ($defaults as $row) {
            DB::table('campaign_statuses')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'color' => $row['color'],
                    'is_default' => $row['is_default'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $statusMap = DB::table('campaign_statuses')->pluck('id', 'code');

        DB::table('campaigns')->orderBy('id')->each(function (object $campaign) use ($statusMap): void {
            $statusId = $statusMap[$campaign->status] ?? $statusMap['draft'] ?? null;

            if ($statusId) {
                DB::table('campaigns')->where('id', $campaign->id)->update([
                    'campaign_status_id' => $statusId,
                ]);
            }
        });

        Schema::table('campaigns', function ($table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function ($table) {
            $table->string('status')->default('draft')->after('expected_patients');
        });

        $statusMap = DB::table('campaign_statuses')->pluck('code', 'id');

        DB::table('campaigns')->orderBy('id')->each(function (object $campaign) use ($statusMap): void {
            DB::table('campaigns')->where('id', $campaign->id)->update([
                'status' => $statusMap[$campaign->campaign_status_id] ?? 'draft',
            ]);
        });

        Schema::table('campaigns', function ($table) {
            $table->dropConstrainedForeignId('campaign_status_id');
        });
    }
};
