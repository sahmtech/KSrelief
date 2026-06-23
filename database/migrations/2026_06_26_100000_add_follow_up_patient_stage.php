<?php

use App\Models\PatientStage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PatientStage::query()->updateOrCreate(
            ['code' => 'follow_up'],
            [
                'name' => 'Follow Up',
                'color' => '#8B5CF6',
                'sort_order' => 8,
                'is_default' => false,
                'status' => 'active',
            ]
        );

        PatientStage::query()
            ->where('code', 'completed')
            ->update(['sort_order' => 9]);
    }

    public function down(): void
    {
        PatientStage::query()->where('code', 'follow_up')->delete();

        PatientStage::query()
            ->where('code', 'completed')
            ->update(['sort_order' => 8]);
    }
};
