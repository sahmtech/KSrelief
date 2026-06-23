<?php

use App\Models\PatientStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (PatientStage::query()->where('code', 'pre_operation')->exists()) {
            return;
        }

        DB::table('patient_stages')
            ->where('sort_order', '>=', 1)
            ->increment('sort_order');

        PatientStage::query()->create([
            'name' => 'Pre Operation',
            'code' => 'pre_operation',
            'color' => '#D9EAD3',
            'sort_order' => 1,
            'is_default' => false,
            'status' => 'active',
        ]);
    }

    public function down(): void
    {
        PatientStage::query()->where('code', 'pre_operation')->delete();

        DB::table('patient_stages')
            ->where('sort_order', '>', 1)
            ->decrement('sort_order');
    }
};
