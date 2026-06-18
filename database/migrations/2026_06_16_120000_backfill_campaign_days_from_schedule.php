<?php

use App\Models\Campaign;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Campaign::query()
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->each(function (Campaign $campaign): void {
                $campaign->updateQuietly([
                    'shifts_count' => $campaign->campaignDaysCount(),
                ]);
            });
    }

    public function down(): void
    {
        // No rollback — previous shifts_count values are not preserved.
    }
};
