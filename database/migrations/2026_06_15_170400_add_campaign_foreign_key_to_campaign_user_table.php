<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_user', function (Blueprint $table) {
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_user', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
        });
    }
};
