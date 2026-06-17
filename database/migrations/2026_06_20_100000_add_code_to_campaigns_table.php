<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table): void {
            $table->string('code', 50)->nullable()->unique()->after('name');
        });

        foreach (DB::table('campaigns')->orderBy('id')->get() as $campaign) {
            DB::table('campaigns')
                ->where('id', $campaign->id)
                ->update([
                    'code' => 'CAMP-'.str_pad((string) $campaign->id, 4, '0', STR_PAD_LEFT),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table): void {
            $table->dropColumn('code');
        });
    }
};
