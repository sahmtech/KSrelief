<?php

use App\Models\PatientStage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $colors = [
            'admission' => '#FFD966',
            'anesthesia' => '#FFF2CC',
            'operation' => '#B6D7A8',
            'post_operation' => '#9FC5E8',
            'activation' => '#6FA8DC',
            'rehab_education' => '#9FC5E8',
            'completed' => '#22C55E',
        ];

        foreach ($colors as $code => $color) {
            PatientStage::query()->where('code', $code)->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        // Colors are cosmetic; no rollback.
    }
};
